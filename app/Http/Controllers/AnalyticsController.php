<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Services\AnalyticsService;
use App\Http\Resources\AnalyticsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    public function __construct(private AnalyticsService $analyticsService)
    {
    }

    /**
     * Get organization-wide analytics dashboard.
     *
     * GET /organizations/{org_id}/analytics
     *
     * @param string $org_id
     * @return JsonResponse
     */
    public function getOrganizationAnalytics(string $org_id): JsonResponse
    {
        $organization = Organization::findOrFail($org_id);

        // Only org members can view analytics
        if (!Auth::user() || !$organization->members()->where('user_id', Auth::user()->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $analytics = $this->analyticsService->getOrganizationAnalytics($organization);

        return response()->json([
            'message' => 'Organization analytics retrieved successfully',
            'data' => new AnalyticsResource($analytics),
        ]);
    }

    /**
     * Get election trends over time.
     *
     * GET /organizations/{org_id}/analytics/trends
     *
     * @param string $org_id
     * @return JsonResponse
     */
    public function getTrends(string $org_id): JsonResponse
    {
        $organization = Organization::findOrFail($org_id);

        // Only org members can view analytics
        if (!Auth::user() || !$organization->members()->where('user_id', Auth::user()->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $trends = $this->analyticsService->getElectionTrends($organization);

        return response()->json([
            'message' => 'Election trends retrieved successfully',
            'data' => new AnalyticsResource($trends),
        ]);
    }

    /**
     * Get member participation analytics.
     *
     * GET /organizations/{org_id}/analytics/participation
     *
     * @param string $org_id
     * @return JsonResponse
     */
    public function getMemberParticipation(string $org_id): JsonResponse
    {
        $organization = Organization::findOrFail($org_id);

        // Only org members can view analytics
        if (!Auth::user() || !$organization->members()->where('user_id', Auth::user()->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $participation = $this->analyticsService->getMemberParticipationAnalytics($organization);

        return response()->json([
            'message' => 'Member participation analytics retrieved successfully',
            'data' => new AnalyticsResource($participation),
        ]);
    }

    /**
     * Get most competitive elections.
     *
     * GET /organizations/{org_id}/analytics/competitive
     *
     * @param string $org_id
     * @return JsonResponse
     */
    public function getMostCompetitive(string $org_id): JsonResponse
    {
        $organization = Organization::findOrFail($org_id);

        // Only org members can view analytics
        if (!Auth::user() || !$organization->members()->where('user_id', Auth::user()->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $limit = (int) request()->query('limit', 5);

        $competitive = $this->analyticsService->getMostCompetitiveElections($organization, $limit);

        return response()->json([
            'message' => 'Competitive elections retrieved successfully',
            'data' => new AnalyticsResource($competitive),
        ]);
    }

    /**
     * Get high turnout elections.
     *
     * GET /organizations/{org_id}/analytics/turnout
     *
     * @param string $org_id
     * @return JsonResponse
     */
    public function getHighTurnout(string $org_id): JsonResponse
    {
        $organization = Organization::findOrFail($org_id);

        // Only org members can view analytics
        if (!Auth::user() || !$organization->members()->where('user_id', Auth::user()->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $limit = (int) request()->query('limit', 5);

        $turnout = $this->analyticsService->getHighTurnoutElections($organization, $limit);

        return response()->json([
            'message' => 'High turnout elections retrieved successfully',
            'data' => new AnalyticsResource($turnout),
        ]);
    }

    /**
     * Get candidate performance analytics.
     *
     * GET /organizations/{org_id}/analytics/candidates
     *
     * @param string $org_id
     * @return JsonResponse
     */
    public function getCandidatePerformance(string $org_id): JsonResponse
    {
        $organization = Organization::findOrFail($org_id);

        // Only org members can view analytics
        if (!Auth::user() || !$organization->members()->where('user_id', Auth::user()->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $performance = $this->analyticsService->getCandidatePerformance($organization);

        return response()->json([
            'message' => 'Candidate performance analytics retrieved successfully',
            'data' => new AnalyticsResource($performance),
        ]);
    }
}
