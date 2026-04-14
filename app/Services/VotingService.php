<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionAccess;
use App\Models\Position;
use App\Models\Vote;
use App\Models\VoteSession;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

class VotingService
{
    public function __construct(private ConnectionInterface $connection)
    {
    }

    /**
     * Submit a single vote with atomic transaction support.
     *
     * @param Election $election
     * @param ElectionAccess $voterToken
     * @param Position $position
     * @param Candidate $candidate
     * @return Vote
     *
     * @throws \Exception
     */
    public function submitVote(
        Election $election,
        ElectionAccess $voterToken,
        Position $position,
        Candidate $candidate,
    ): Vote {
        return DB::transaction(function () use (
            $election,
            $voterToken,
            $position,
            $candidate,
        ) {
            // Validate election is active
            if (!$election->isActive()) {
                throw new \Exception('Election is not currently active', 400);
            }

            // Validate voter token hasn't expired
            if ($voterToken->hasExpired()) {
                throw new \Exception('Voter token has expired', 403);
            }

            // Validate token belongs to this election
            if ($voterToken->election_id !== $election->id) {
                throw new \Exception('Voter token is not valid for this election', 403);
            }

            // Validate position belongs to this election
            if ($position->election_id !== $election->id) {
                throw new \Exception('Position does not belong to this election', 422);
            }

            // Validate candidate belongs to this position
            if ($candidate->position_id !== $position->id) {
                throw new \Exception('Candidate does not belong to this position', 422);
            }

            // Get or create vote session (uses row-level locking)
            $voteSession = $this->getOrCreateVoteSession($election, $voterToken);

            // Check voter hasn't already voted for this position
            $existingVote = $this->hasVotedForPosition($voteSession, $position);
            if ($existingVote) {
                throw new \Exception('You have already voted for this position', 409);
            }

            // Check voter hasn't exceeded max_votes for this position
            $voteCount = $voteSession->votes()
                ->where('position_id', $position->id)
                ->count();

            if ($voteCount >= $position->max_votes) {
                throw new \Exception(
                    "You can only vote for {$position->max_votes} candidate(s) in this position",
                    422,
                );
            }

            // Create the vote
            $vote = Vote::create([
                'vote_session_id' => $voteSession->id,
                'position_id' => $position->id,
                'candidate_id' => $candidate->id,
            ]);

            return $vote;
        }, attempts: 5);
    }

    /**
     * Submit batch votes in a single atomic transaction.
     *
     * @param Election $election
     * @param ElectionAccess $voterToken
     * @param array $votes Array of ['position_id' => int, 'candidate_id' => int]
     * @return array Array of created Vote models
     *
     * @throws \Exception
     */
    public function submitBatchVotes(
        Election $election,
        ElectionAccess $voterToken,
        array $votes,
    ): array {
        return DB::transaction(function () use ($election, $voterToken, $votes) {
            // Validate election is active
            if (!$election->isActive()) {
                throw new \Exception('Election is not currently active', 400);
            }

            // Validate voter token
            if ($voterToken->hasExpired()) {
                throw new \Exception('Voter token has expired', 403);
            }

            if ($voterToken->election_id !== $election->id) {
                throw new \Exception('Voter token is not valid for this election', 403);
            }

            // Get or create vote session
            $voteSession = $this->getOrCreateVoteSession($election, $voterToken);

            $createdVotes = [];
            $positionVoteCounts = [];

            // Validate and count votes per position first
            foreach ($votes as $vote) {
                $positionId = $vote['position_id'] ?? null;
                $candidateId = $vote['candidate_id'] ?? null;

                if (!$positionId || !$candidateId) {
                    throw new \Exception('Invalid vote format: missing position_id or candidate_id', 422);
                }

                if (!isset($positionVoteCounts[$positionId])) {
                    $positionVoteCounts[$positionId] = 0;
                }
                $positionVoteCounts[$positionId]++;
            }

            // Validate positions and check max_votes constraint
            $positions = $election->positions()
                ->whereIn('id', array_keys($positionVoteCounts))
                ->get()
                ->keyBy('id');

            foreach ($positionVoteCounts as $positionId => $count) {
                if (!isset($positions[$positionId])) {
                    throw new \Exception("Position {$positionId} not found in this election", 422);
                }

                $position = $positions[$positionId];
                $existingVotes = $voteSession->votes()
                    ->where('position_id', $positionId)
                    ->count();

                if ($existingVotes + $count > $position->max_votes) {
                    throw new \Exception(
                        "You can only vote for {$position->max_votes} candidate(s) in {$position->title}",
                        422,
                    );
                }
            }

            // Create all votes
            foreach ($votes as $vote) {
                $positionId = $vote['position_id'];
                $candidateId = $vote['candidate_id'];

                $position = $positions[$positionId];

                // Validate candidate belongs to position
                $candidate = $position->candidates()
                    ->where('id', $candidateId)
                    ->first();

                if (!$candidate) {
                    throw new \Exception(
                        "Candidate {$candidateId} not found in position {$positionId}",
                        422,
                    );
                }

                // Check for duplicate votes in same request
                $alreadyVoted = collect($createdVotes)
                    ->contains(function (Vote $v) use ($positionId, $candidateId) {
                        return $v->position_id === $positionId && $v->candidate_id === $candidateId;
                    });

                if ($alreadyVoted) {
                    throw new \Exception(
                        "Cannot vote for the same candidate twice in one submission",
                        422,
                    );
                }

                $createdVote = Vote::create([
                    'vote_session_id' => $voteSession->id,
                    'position_id' => $positionId,
                    'candidate_id' => $candidateId,
                ]);

                $createdVotes[] = $createdVote;
            }

            return $createdVotes;
        }, attempts: 5);
    }

    /**
     * Get or create a vote session with row-level locking for race conditions.
     *
     * @param Election $election
     * @param ElectionAccess $voterToken
     * @return VoteSession
     */
    protected function getOrCreateVoteSession(Election $election, ElectionAccess $voterToken): VoteSession
    {
        // Check for existing session (with row-level lock to prevent race conditions)
        $existingSession = $election->voteSessions()
            ->where('voter_token', $voterToken->token)
            ->lockForUpdate()
            ->first();

        if ($existingSession) {
            return $existingSession;
        }

        // Create new session atomically
        $voteSession = VoteSession::create([
            'election_id' => $election->id,
            'voter_token' => $voterToken->token,
            'submitted_at' => now(),
        ]);

        // Mark token as used after creating session
        $voterToken->update(['used_at' => now()]);

        return $voteSession;
    }

    /**
     * Check if voter has already voted for a position.
     *
     * @param VoteSession $voteSession
     * @param Position $position
     * @return bool
     */
    protected function hasVotedForPosition(VoteSession $voteSession, Position $position): bool
    {
        return $voteSession->votes()
            ->where('position_id', $position->id)
            ->exists();
    }

    /**
     * Get voting statistics for an election (before publication).
     *
     * @param Election $election
     * @return array
     */
    public function getVotingStats(Election $election): array
    {
        $totalVoters = $election->accessTokens()->count();
        $totalVoted = $election->voteSessions()->count();
        $turnout = $totalVoters > 0 ? ($totalVoted / $totalVoters) * 100 : 0;

        return [
            'total_voters' => $totalVoters,
            'total_voted' => $totalVoted,
            'turnout_percentage' => round($turnout, 2),
            'total_votes' => $election->votes()->count(),
        ];
    }

    /**
     * Validate that a candidate can receive votes.
     *
     * @param Candidate $candidate
     * @return bool
     */
    public function isValidCandidate(Candidate $candidate): bool
    {
        $position = $candidate->position;

        return $position && $position->election->isActive();
    }

    /**
     * Revoke all votes from a voter (if election hasn't been published).
     *
     * @param Election $election
     * @param ElectionAccess $voterToken
     * @return int Number of votes deleted
     *
     * @throws \Exception
     */
    public function revokeVoterVotes(Election $election, ElectionAccess $voterToken): int
    {
        if ($election->status === 'published') {
            throw new \Exception('Cannot revoke votes from a published election', 403);
        }

        $voteSession = $election->voteSessions()
            ->where('voter_token_id', $voterToken->id)
            ->first();

        if (!$voteSession) {
            return 0;
        }

        $count = $voteSession->votes()->count();
        $voteSession->votes()->delete();
        $voteSession->delete();

        // Reset token status
        $voterToken->update(['used_at' => null]);

        return $count;
    }
}
