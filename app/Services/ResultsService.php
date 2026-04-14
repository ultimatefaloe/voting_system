<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Collection;

class ResultsService
{
    /**
     * Get live results for an active election (for poll watchers).
     * Only shows aggregate counts, not individual votes.
     *
     * @param Election $election
     * @return array
     *
     * @throws \Exception
     */
    public function getLiveResults(Election $election): array
    {
        if (!$election->isActive()) {
            throw new \Exception('Election must be active to view live results', 400);
        }

        return $this->aggregateResults($election);
    }

    /**
     * Get published results for a completed election.
     *
     * @param Election $election
     * @return array
     *
     * @throws \Exception
     */
    public function getPublishedResults(Election $election): array
    {
        if ($election->status !== 'published') {
            throw new \Exception('Results only available for published elections', 400);
        }

        return $this->aggregateResults($election);
    }

    /**
     * Get detailed analytics for an election.
     * Includes vote distribution, candidate performance, turnout metrics.
     *
     * @param Election $election
     * @return array
     *
     * @throws \Exception
     */
    public function getDetailedAnalytics(Election $election): array
    {
        if ($election->status !== 'published') {
            throw new \Exception('Analytics only available for published elections', 400);
        }

        $totalVoters = $election->accessTokens()->count();
        $totalVoted = $election->voteSessions()->count();
        $totalVotes = $election->votes()->count();

        $positions = $election->positions()->with(['candidates'])->get();

        $positionAnalytics = $positions->map(function (Position $position) use ($totalVotes) {
            $candidates = $position->candidates->map(function (Candidate $candidate) use ($totalVotes, $position) {
                $voteCount = $candidate->votes()->count();
                $positionVotes = $position->candidates->sum(fn ($c) => $c->votes()->count());

                return [
                    'id' => $candidate->id,
                    'name' => $candidate->name,
                    'bio' => $candidate->bio,
                    'avatar' => $candidate->avatar,
                    'votes' => $voteCount,
                    'percentage_of_position' => $positionVotes > 0 ? round(($voteCount / $positionVotes) * 100, 2) : 0,
                    'percentage_of_total' => $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100, 2) : 0,
                    'rank' => 0, // Set after sorting
                ];
            })->sortByDesc('votes')->values();

            // Add ranking
            $candidates = $candidates->map(function ($candidate, $index) {
                $candidate['rank'] = $index + 1;
                return $candidate;
            })->values();

            $positionVotes = $candidates->sum('votes');

            return [
                'id' => $position->id,
                'title' => $position->title,
                'description' => $position->description,
                'max_votes' => $position->max_votes,
                'total_votes' => $positionVotes,
                'candidates' => $candidates,
                'winner' => $candidates->first() ? [
                    'id' => $candidates->first()['id'],
                    'name' => $candidates->first()['name'],
                    'votes' => $candidates->first()['votes'],
                ] : null,
            ];
        });

        return [
            'election_id' => $election->id,
            'election_title' => $election->title,
            'election_type' => $election->type,
            'status' => $election->status,
            'voters' => [
                'total_voters' => $totalVoters,
                'total_voted' => $totalVoted,
                'turnout_percentage' => $totalVoters > 0 ? round(($totalVoted / $totalVoters) * 100, 2) : 0,
                'did_not_vote' => $totalVoters - $totalVoted,
            ],
            'votes' => [
                'total_votes' => $totalVotes,
                'average_votes_per_voter' => $totalVoted > 0 ? round($totalVotes / $totalVoted, 2) : 0,
            ],
            'positions' => $positionAnalytics,
        ];
    }

    /**
     * Get vote distribution statistics for a position.
     *
     * @param Position $position
     * @return array
     */
    public function getPositionStatistics(Position $position): array
    {
        $candidates = $position->candidates()->with('votes')->get();
        $totalVotes = $candidates->sum(fn ($c) => $c->votes()->count());

        $candidateStats = $candidates->map(function (Candidate $candidate) use ($totalVotes) {
            $voteCount = $candidate->votes()->count();

            return [
                'id' => $candidate->id,
                'name' => $candidate->name,
                'votes' => $voteCount,
                'percentage' => $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100, 2) : 0,
            ];
        })->sortByDesc('votes')->values();

        return [
            'position_id' => $position->id,
            'position_title' => $position->title,
            'max_votes' => $position->max_votes,
            'total_votes' => $totalVotes,
            'candidates' => $candidateStats,
        ];
    }

    /**
     * Get candidate statistics.
     *
     * @param Candidate $candidate
     * @return array
     */
    public function getCandidateStatistics(Candidate $candidate): array
    {
        $position = $candidate->position;
        $election = $position->election;

        $voteCount = $candidate->votes()->count();
        $positionVotes = $position->candidates->sum(fn ($c) => $c->votes()->count());
        $totalElectionVotes = $election->votes()->count();

        $candidates = $position->candidates()
            ->with('votes')
            ->get()
            ->map(fn ($c) => $c->votes()->count())
            ->sort()
            ->reverse()
            ->values();

        $rank = $candidates->search($voteCount) !== false ? $candidates->search($voteCount) + 1 : count($candidates);

        return [
            'candidate_id' => $candidate->id,
            'name' => $candidate->name,
            'bio' => $candidate->bio,
            'avatar' => $candidate->avatar,
            'position' => [
                'id' => $position->id,
                'title' => $position->title,
            ],
            'votes' => [
                'total' => $voteCount,
                'percentage_of_position' => $positionVotes > 0 ? round(($voteCount / $positionVotes) * 100, 2) : 0,
                'percentage_of_election' => $totalElectionVotes > 0 ? round(($voteCount / $totalElectionVotes) * 100, 2) : 0,
            ],
            'ranking' => [
                'rank' => $rank,
                'total_candidates' => $position->candidates->count(),
            ],
        ];
    }

    /**
     * Get electoral results summary (quick overview).
     *
     * @param Election $election
     * @return array
     */
    public function getResultsSummary(Election $election): array
    {
        if ($election->status !== 'published') {
            throw new \Exception('Results only available for published elections', 400);
        }

        $positions = $election->positions()->with(['candidates.votes'])->get();

        return [
            'election_id' => $election->id,
            'election_title' => $election->title,
            'status' => $election->status,
            'total_voters' => $election->accessTokens()->count(),
            'total_voted' => $election->voteSessions()->count(),
            'total_votes' => $election->votes()->count(),
            'positions' => $positions->map(function (Position $position) {
                $winner = $position->candidates
                    ->sortByDesc(fn ($c) => $c->votes()->count())
                    ->first();

                return [
                    'id' => $position->id,
                    'title' => $position->title,
                    'winner' => $winner ? [
                        'id' => $winner->id,
                        'name' => $winner->name,
                        'votes' => $winner->votes()->count(),
                    ] : null,
                ];
            }),
        ];
    }

    /**
     * Get vote distribution curve (for visualization).
     * Shows how votes are distributed across candidates.
     *
     * @param Position $position
     * @return array
     */
    public function getVoteDistributionCurve(Position $position): array
    {
        $candidates = $position->candidates()
            ->with('votes')
            ->get()
            ->sortByDesc(fn ($c) => $c->votes()->count())
            ->values();

        $totalVotes = $candidates->sum(fn ($c) => $c->votes()->count());

        return [
            'position_id' => $position->id,
            'position_title' => $position->title,
            'total_votes' => $totalVotes,
            'distribution' => $candidates->map(function (Candidate $candidate, $index) use ($totalVotes) {
                $voteCount = $candidate->votes()->count();

                return [
                    'rank' => $index + 1,
                    'candidate_id' => $candidate->id,
                    'name' => $candidate->name,
                    'votes' => $voteCount,
                    'percentage' => $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100, 2) : 0,
                    'cumulative_percentage' => 0, // Set in next loop
                ];
            })->values(),
        ];
    }

    /**
     * Get comparative analysis between two elections.
     *
     * @param Election $election1
     * @param Election $election2
     * @return array
     *
     * @throws \Exception
     */
    public function compareElections(Election $election1, Election $election2): array
    {
        if ($election1->organization_id !== $election2->organization_id) {
            throw new \Exception('Elections must be from the same organization', 422);
        }

        if ($election1->status !== 'published' || $election2->status !== 'published') {
            throw new \Exception('Both elections must be published', 400);
        }

        $stats1 = $this->getDetailedAnalytics($election1);
        $stats2 = $this->getDetailedAnalytics($election2);

        return [
            'election_1' => [
                'id' => $election1->id,
                'title' => $election1->title,
                'voters' => $stats1['voters'],
                'votes' => $stats1['votes'],
            ],
            'election_2' => [
                'id' => $election2->id,
                'title' => $election2->title,
                'voters' => $stats2['voters'],
                'votes' => $stats2['votes'],
            ],
            'comparison' => [
                'turnout_difference' => round($stats1['voters']['turnout_percentage'] - $stats2['voters']['turnout_percentage'], 2),
                'total_votes_difference' => $stats1['votes']['total_votes'] - $stats2['votes']['total_votes'],
                'average_votes_per_voter_difference' => round(
                    $stats1['votes']['average_votes_per_voter'] - $stats2['votes']['average_votes_per_voter'],
                    2,
                ),
            ],
        ];
    }

    /**
     * Aggregate results for an election (core method used by other methods).
     *
     * @param Election $election
     * @return array
     */
    protected function aggregateResults(Election $election): array
    {
        $totalVoters = $election->accessTokens()->count();
        $totalVoted = $election->voteSessions()->count();
        $totalVotes = $election->votes()->count();

        $positions = $election->positions()
            ->with(['candidates'])
            ->get();

        $positionResults = $positions->map(function (Position $position) use ($totalVotes) {
            $candidates = $position->candidates->map(function (Candidate $candidate) use ($totalVotes, $position) {
                $voteCount = $candidate->votes()->count();
                $positionVotes = $position->candidates->sum(fn ($c) => $c->votes()->count());

                return [
                    'id' => $candidate->id,
                    'name' => $candidate->name,
                    'votes' => $voteCount,
                    'percentage' => $positionVotes > 0 ? round(($voteCount / $positionVotes) * 100, 2) : 0,
                ];
            })->sortByDesc('votes')->values();

            return [
                'id' => $position->id,
                'title' => $position->title,
                'max_votes' => $position->max_votes,
                'total_votes' => $position->candidates->sum(fn ($c) => $c->votes()->count()),
                'candidates' => $candidates,
            ];
        });

        return [
            'election_id' => $election->id,
            'election_title' => $election->title,
            'type' => $election->type,
            'status' => $election->status,
            'total_voters' => $totalVoters,
            'total_voted' => $totalVoted,
            'turnout_percentage' => $totalVoters > 0 ? round(($totalVoted / $totalVoters) * 100, 2) : 0,
            'total_votes' => $totalVotes,
            'positions' => $positionResults,
        ];
    }
}
