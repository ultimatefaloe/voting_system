<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\OrganizationMember;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsPageController extends Controller
{
    public function __invoke(): Response
    {
        $user = request()->user();

        $organizationIds = OrganizationMember::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('organization_id');

        $publishedElectionsBase = Election::query()
            ->whereIn('organization_id', $organizationIds)
            ->where('status', 'published');

        $publishedElections = (clone $publishedElectionsBase)
            ->with(['organization:id,name'])
            ->withCount(['positions', 'voteSessions', 'votes'])
            ->orderByDesc('updated_at')
            ->get();

        $topElections = $publishedElections
            ->sortByDesc('votes_count')
            ->take(8)
            ->map(function (Election $election) {
                return [
                    'id' => $election->id,
                    'title' => $election->title,
                    'organization' => $election->organization?->name,
                    'votes_count' => $election->votes_count,
                    'vote_sessions_count' => $election->vote_sessions_count,
                    'positions_count' => $election->positions_count,
                ];
            })
            ->values();

        $recentPublished = $publishedElections
            ->take(8)
            ->map(function (Election $election) {
                return [
                    'id' => $election->id,
                    'title' => $election->title,
                    'organization' => $election->organization?->name,
                    'start_date' => $election->start_date?->toDateString(),
                    'end_date' => $election->end_date?->toDateString(),
                    'votes_count' => $election->votes_count,
                    'vote_sessions_count' => $election->vote_sessions_count,
                ];
            })
            ->values();

        $topCandidates = Candidate::query()
            ->whereHas('position.election', function ($query) use ($organizationIds) {
                $query->whereIn('organization_id', $organizationIds)
                    ->where('status', 'published');
            })
            ->withCount('votes')
            ->with(['position:id,election_id,title', 'position.election:id,organization_id,title'])
            ->orderByDesc('votes_count')
            ->limit(8)
            ->get()
            ->map(function (Candidate $candidate) {
                return [
                    'id' => $candidate->id,
                    'name' => $candidate->name,
                    'votes_count' => $candidate->votes_count,
                    'position' => $candidate->position?->title,
                    'election' => $candidate->position?->election?->title,
                ];
            })
            ->values();

        return Inertia::render('analytics/index', [
            'analyticsData' => [
                'summary' => [
                    'published_elections' => $publishedElections->count(),
                    'total_votes' => $publishedElections->sum('votes_count'),
                    'total_vote_sessions' => $publishedElections->sum('vote_sessions_count'),
                    'average_votes_per_election' => $publishedElections->count() > 0
                        ? round($publishedElections->sum('votes_count') / $publishedElections->count(), 2)
                        : 0,
                ],
                'top_elections' => $topElections,
                'top_candidates' => $topCandidates,
                'recent_published' => $recentPublished,
            ],
        ]);
    }
}
