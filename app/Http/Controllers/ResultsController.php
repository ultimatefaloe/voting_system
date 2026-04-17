<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Services\ResultsService;
use App\Http\Resources\ResultsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResultsController extends Controller
{
    public function __construct(private ResultsService $resultsService)
    {
    }

    /**
     * Get live results for an active election (poll watchers).
     * Real-time vote counts for ongoing elections.
     *
     * GET /organizations/{org_id}/elections/{election_id}/results/live
     *
     * @param string $org_id
     * @param string $election_id
     * @return JsonResponse
     */
    public function getLiveResults(string $org_id, string $election_id): JsonResponse
    {
        $election = Election::where('organization_id', $org_id)
            ->where('id', $election_id)
            ->firstOrFail();

        // Only org members can view live results
        if (!request()->user() || !$election->organization->members()->where('user_id', request()->user()->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $results = $this->resultsService->getLiveResults($election);

            return response()->json([
                'message' => 'Live results retrieved successfully',
                'data' => new ResultsResource($results),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422);
        }
    }

    /**
     * Get published election results.
     *
     * GET /elections/{election_id}/results
     *
     * @param string $election_id
     * @return JsonResponse
     */
    public function getResults(string $election_id): JsonResponse
    {
        $election = Election::where('id', $election_id)->firstOrFail();

        // Check access permissions
        if ($election->type === 'private') {
            $organization = $election->organization;
            if (!request()->user() || !$organization->members()->where('user_id', request()->user()->id)->exists()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        try {
            $results = $this->resultsService->getPublishedResults($election);

            return response()->json([
                'message' => 'Results retrieved successfully',
                'data' => new ResultsResource($results),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422);
        }
    }

    /**
     * Get results summary (winners only).
     *
     * GET /elections/{election_id}/results/summary
     *
     * @param string $election_id
     * @return JsonResponse
     */
    public function getResultsSummary(string $election_id): JsonResponse
    {
        $election = Election::where('id', $election_id)->firstOrFail();

        // Check access permissions
        if ($election->type === 'private') {
            $organization = $election->organization;
            if (!request()->user() || !$organization->members()->where('user_id', request()->user()->id)->exists()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        try {
            $summary = $this->resultsService->getResultsSummary($election);

            return response()->json([
                'message' => 'Results summary retrieved successfully',
                'data' => new ResultsResource($summary),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422);
        }
    }

    /**
     * Get detailed analytics for a published election.
     * Includes turnout, vote distribution, candidate rankings, etc.
     *
     * GET /organizations/{org_id}/elections/{election_id}/analytics
     *
     * @param string $org_id
     * @param string $election_id
     * @return JsonResponse
     */
    public function getAnalytics(string $org_id, string $election_id): JsonResponse
    {
        $election = Election::where('organization_id', $org_id)
            ->where('id', $election_id)
            ->firstOrFail();

        // Only org members can view analytics
        if (!request()->user() || !$election->organization->members()->where('user_id', request()->user()->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $analytics = $this->resultsService->getDetailedAnalytics($election);

            return response()->json([
                'message' => 'Analytics retrieved successfully',
                'data' => new ResultsResource($analytics),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422);
        }
    }

    /**
     * Get position-specific statistics.
     *
     * GET /organizations/{org_id}/elections/{election_id}/positions/{position_id}/statistics
     *
     * @param string $org_id
     * @param string $election_id
     * @param string $position_id
     * @return JsonResponse
     */
    public function getPositionStatistics(string $org_id, string $election_id, string $position_id): JsonResponse
    {
        $election = Election::where('organization_id', $org_id)
            ->where('id', $election_id)
            ->firstOrFail();

        $position = Position::where('election_id', $election->id)
            ->where('id', $position_id)
            ->firstOrFail();

        // Check access permissions
        if ($election->type === 'private') {
            if (!request()->user() || !$election->organization->members()->where('user_id', request()->user()->id)->exists()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $stats = $this->resultsService->getPositionStatistics($position);

        return response()->json([
            'message' => 'Position statistics retrieved successfully',
            'data' => new ResultsResource($stats),
        ]);
    }

    /**
     * Get candidate-specific statistics.
     *
     * GET /organizations/{org_id}/elections/{election_id}/positions/{position_id}/candidates/{candidate_id}/statistics
     *
     * @param string $org_id
     * @param string $election_id
     * @param string $position_id
     * @param string $candidate_id
     * @return JsonResponse
     */
    public function getCandidateStatistics(
        string $org_id,
        string $election_id,
        string $position_id,
        string $candidate_id,
    ): JsonResponse {
        $election = Election::where('organization_id', $org_id)
            ->where('id', $election_id)
            ->firstOrFail();

        $position = Position::where('election_id', $election->id)
            ->where('id', $position_id)
            ->firstOrFail();

        $candidate = Candidate::where('position_id', $position->id)
            ->where('id', $candidate_id)
            ->firstOrFail();

        // Check access permissions
        if ($election->type === 'private') {
            if (!request()->user() || !$election->organization->members()->where('user_id', request()->user()->id)->exists()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $stats = $this->resultsService->getCandidateStatistics($candidate);

        return response()->json([
            'message' => 'Candidate statistics retrieved successfully',
            'data' => new ResultsResource($stats),
        ]);
    }

    /**
     * Get vote distribution curve for visualization.
     *
     * GET /organizations/{org_id}/elections/{election_id}/positions/{position_id}/distribution
     *
     * @param string $org_id
     * @param string $election_id
     * @param string $position_id
     * @return JsonResponse
     */
    public function getVoteDistribution(string $org_id, string $election_id, string $position_id): JsonResponse
    {
        $election = Election::where('organization_id', $org_id)
            ->where('id', $election_id)
            ->firstOrFail();

        $position = Position::where('election_id', $election->id)
            ->where('id', $position_id)
            ->firstOrFail();

        // Check access permissions
        if ($election->type === 'private') {
            if (!request()->user() || !$election->organization->members()->where('user_id', request()->user()->id)->exists()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $distribution = $this->resultsService->getVoteDistributionCurve($position);

        return response()->json([
            'message' => 'Vote distribution retrieved successfully',
            'data' => new ResultsResource($distribution),
        ]);
    }

    /**
     * Compare two published elections.
     *
     * POST /organizations/{org_id}/elections/compare
     * {
     *   "election_id_1": 1,
     *   "election_id_2": 2
     * }
     *
     * @param string $org_id
     * @return JsonResponse
     */
    public function compareElections(string $org_id): JsonResponse
    {
        // Only org members can compare
        if (!request()->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $organization = request()->attributes->get('organization');
        if (!$organization || !$organization->members()->where('user_id', request()->user()->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = request()->validate([
            'election_id_1' => 'required|integer|exists:elections,id',
            'election_id_2' => 'required|integer|exists:elections,id',
        ]);

        $election1 = Election::findOrFail($validated['election_id_1']);
        $election2 = Election::findOrFail($validated['election_id_2']);

        if ($election1->organization_id !== $organization->id || $election2->organization_id !== $organization->id) {
            return response()->json(['message' => 'Elections must belong to the organization'], 422);
        }

        try {
            $comparison = $this->resultsService->compareElections($election1, $election2);

            return response()->json([
                'message' => 'Elections compared successfully',
                'data' => new ResultsResource($comparison),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422);
        }
    }

    /**
     * Export election results (placeholder for Phase 7+).
     * Will support CSV, JSON, PDF formats.
     *
     * GET /elections/{election_id}/results/export?format=csv
     *
     * @param string $election_id
     * @return JsonResponse
     */
    public function exportResults(string $election_id): JsonResponse
    {
        $election = Election::where('id', $election_id)->firstOrFail();

        $format = request()->query('format', 'json');

        // Check access
        if ($election->type === 'private') {
            if (!request()->user() || !$election->organization->members()->where('user_id', request()->user()->id)->exists()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        try {
            $results = $this->resultsService->getPublishedResults($election);

            // TODO: Implement actual export formats in Phase 7+
            return response()->json([
                'message' => 'Results exported successfully',
                'data' => new ResultsResource($results),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422);
        }
    }
}
