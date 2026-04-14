<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionAccess;
use App\Models\Position;
use App\Models\Vote;
use App\Services\VotingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VoteController extends Controller
{
    public function __construct(private VotingService $votingService)
    {
    }

    /**
     * Get voting statistics for an election.
     * Requires org membership to view stats (before publication).
     *
     * GET /organizations/{org_id}/elections/{election_id}/voting-stats
     *
     * @param string $org_id
     * @param string $election_id
     * @return JsonResponse
     */
    public function stats(string $org_id, string $election_id): JsonResponse
    {
        $election = Election::where('organization_id', $org_id)
            ->where('id', $election_id)
            ->firstOrFail();

        // Only org members can view voting stats before publication
        if ($election->status !== 'published') {
            $organization = $election->organization;
            if (!request()->user() || !$organization->members()->where('user_id', request()->user()->id)->exists()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $stats = $this->votingService->getVotingStats($election);

        return response()->json($stats);
    }

    /**
     * Get a voter's current ballot (for resuming voting).
     * Requires valid voter token via X-Voter-Token header.
     *
     * GET /elections/{election_id}/ballot
     *
     * @param string $election_id
     * @return JsonResponse
     */
    public function getBallot(string $election_id): JsonResponse
    {
        $election = Election::where('id', $election_id)->firstOrFail();

        // Get voter token from middleware
        $voterToken = request()->attributes->get('voter_token');
        if (!$voterToken || !$voterToken instanceof ElectionAccess) {
            return response()->json(
                ['message' => 'Valid voter token required (X-Voter-Token header)'],
                401,
            );
        }

        // Get or create vote session
        $voteSession = $election->voteSessions()
            ->where('voter_token', $voterToken->token)
            ->with(['votes.candidate.position'])
            ->first();

        if (!$voteSession) {
            // Return empty ballot
            return response()->json([
                'election_id' => $election->id,
                'positions' => $election->positions()
                    ->with(['candidates'])
                    ->get()
                    ->map(fn ($position) => [
                        'id' => $position->id,
                        'title' => $position->title,
                        'description' => $position->description,
                        'max_votes' => $position->max_votes,
                        'candidates' => $position->candidates->map(fn ($candidate) => [
                            'id' => $candidate->id,
                            'name' => $candidate->name,
                            'bio' => $candidate->bio,
                            'avatar' => $candidate->avatar,
                        ]),
                        'selected_candidates' => [],
                    ]),
            ]);
        }

        // Group votes by position
        $votesByPosition = $voteSession->votes->groupBy('position_id');

        return response()->json([
            'election_id' => $election->id,
            'positions' => $election->positions()
                ->with(['candidates'])
                ->get()
                ->map(fn ($position) => [
                    'id' => $position->id,
                    'title' => $position->title,
                    'description' => $position->description,
                    'max_votes' => $position->max_votes,
                    'candidates' => $position->candidates->map(fn ($candidate) => [
                        'id' => $candidate->id,
                        'name' => $candidate->name,
                        'bio' => $candidate->bio,
                        'avatar' => $candidate->avatar,
                    ]),
                    'selected_candidates' => $votesByPosition->get($position->id, collect())->pluck('candidate_id')->values(),
                ]),
        ]);
    }

    /**
     * Submit a single vote for a candidate in a position.
     * Requires valid voter token via X-Voter-Token header.
     *
     * POST /elections/{election_id}/votes
     * {
     *   "position_id": 1,
     *   "candidate_id": 3
     * }
     *
     * @param string $election_id
     * @return JsonResponse
     */
    public function submitVote(string $election_id): JsonResponse
    {
        $election = Election::where('id', $election_id)->firstOrFail();

        // Get voter token from middleware
        $voterToken = request()->attributes->get('voter_token');
        if (!$voterToken || !$voterToken instanceof ElectionAccess) {
            return response()->json(
                ['message' => 'Valid voter token required (X-Voter-Token header)'],
                401,
            );
        }

        // Validate request
        $validated = request()->validate([
            'position_id' => 'required|integer|exists:positions,id',
            'candidate_id' => 'required|integer|exists:candidates,id',
        ]);

        try {
            $position = Position::findOrFail($validated['position_id']);
            $candidate = Candidate::findOrFail($validated['candidate_id']);

            $vote = $this->votingService->submitVote(
                $election,
                $voterToken,
                $position,
                $candidate,
            );

            return response()->json(
                [
                    'message' => 'Vote recorded successfully',
                    'vote' => [
                        'id' => $vote->id,
                        'position_id' => $vote->position_id,
                        'candidate_id' => $vote->candidate_id,
                        'created_at' => $vote->created_at,
                    ],
                ],
                201,
            );
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 422;

            return response()->json(
                ['message' => $e->getMessage()],
                $statusCode,
            );
        }
    }

    /**
     * Submit batch votes in a single transaction.
     * Requires valid voter token via X-Voter-Token header.
     *
     * POST /elections/{election_id}/votes/batch
     * {
     *   "votes": [
     *     {"position_id": 1, "candidate_id": 3},
     *     {"position_id": 2, "candidate_id": 5},
     *     {"position_id": 2, "candidate_id": 7}
     *   ]
     * }
     *
     * @param string $election_id
     * @return JsonResponse
     */
    public function submitBatchVotes(string $election_id): JsonResponse
    {
        $election = Election::where('id', $election_id)->firstOrFail();

        // Get voter token from middleware
        $voterToken = request()->attributes->get('voter_token');
        if (!$voterToken || !$voterToken instanceof ElectionAccess) {
            return response()->json(
                ['message' => 'Valid voter token required (X-Voter-Token header)'],
                401,
            );
        }

        // Validate request
        $validated = request()->validate([
            'votes' => 'required|array|min:1',
            'votes.*.position_id' => 'required|integer|exists:positions,id',
            'votes.*.candidate_id' => 'required|integer|exists:candidates,id',
        ]);

        try {
            $votes = $this->votingService->submitBatchVotes(
                $election,
                $voterToken,
                $validated['votes'],
            );

            return response()->json(
                [
                    'message' => 'Batch votes recorded successfully',
                    'votes_count' => count($votes),
                    'votes' => collect($votes)->map(fn ($vote) => [
                        'id' => $vote->id,
                        'position_id' => $vote->position_id,
                        'candidate_id' => $vote->candidate_id,
                        'created_at' => $vote->created_at,
                    ])->values(),
                ],
                201,
            );
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 422;

            return response()->json(
                ['message' => $e->getMessage()],
                $statusCode,
            );
        }
    }

    /**
     * Get election results after publication.
     * Public endpoint if election is published and public.
     * Requires org membership if election is private.
     *
     * GET /elections/{election_id}/results
     *
     * @param string $election_id
     * @return JsonResponse
     */
    public function getResults(string $election_id): JsonResponse
    {
        $election = Election::where('id', $election_id)->firstOrFail();

        // Check publication status
        if ($election->status !== 'published') {
            return response()->json(['message' => 'Results not available until election is published'], 403);
        }

        // Check access permissions for private elections
        if ($election->type === 'private') {
            $organization = $election->organization;
            if (!request()->user() || !$organization->members()->where('user_id', request()->user()->id)->exists()) {
                return response()->json(['message' => 'Unauthorized to view results'], 403);
            }
        }

        $results = [
            'election_id' => $election->id,
            'election_title' => $election->title,
            'type' => $election->type,
            'status' => $election->status,
            'total_votes' => $election->votes()->count(),
            'total_voters' => $election->voteSessions()->count(),
            'positions' => $election->positions()
                ->with(['candidates'])
                ->get()
                ->map(function ($position) {
                    $totalPositionVotes = $position->candidates->sum(fn ($c) => $c->votes()->count());
                    $candidates = $position->candidates->map(function ($candidate) use ($totalPositionVotes) {
                        $voteCount = $candidate->votes()->count();

                        return [
                            'id' => $candidate->id,
                            'name' => $candidate->name,
                            'votes' => $voteCount,
                            'percentage' => $totalPositionVotes > 0
                                ? round(
                                    ($voteCount / $totalPositionVotes) * 100,
                                    2,
                                )
                                : 0,
                        ];
                    })->sortByDesc('votes')->values();

                    return [
                        'id' => $position->id,
                        'title' => $position->title,
                        'max_votes' => $position->max_votes,
                        'total_votes' => $position->candidates->sum(fn ($c) => $c->votes()->count()),
                        'candidates' => $candidates,
                    ];
                }),
        ];

        return response()->json($results);
    }
}
