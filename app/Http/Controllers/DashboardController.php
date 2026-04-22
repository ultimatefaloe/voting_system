<?php

namespace App\Http\Controllers;

use App\Models\Election;
use App\Models\ElectionAccess;
use App\Models\OrganizationMember;
use App\Models\VoteSession;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $user = request()->user();

        $organizationIds = OrganizationMember::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('organization_id');

        $electionsBase = Election::query()->whereIn('organization_id', $organizationIds);

        $activeElections = (clone $electionsBase)
            ->where('status', 'active')
            ->count();

        $totalElections = (clone $electionsBase)->count();

        $registeredVoters = ElectionAccess::query()
            ->whereHas('election', fn ($query) => $query->whereIn('organization_id', $organizationIds))
            ->count();

        $totalVoted = VoteSession::query()
            ->whereHas('election', fn ($query) => $query->whereIn('organization_id', $organizationIds))
            ->distinct('voter_token')
            ->count('voter_token');

        $votesToday = VoteSession::query()
            ->whereHas('election', fn ($query) => $query->whereIn('organization_id', $organizationIds))
            ->whereDate('submitted_at', now()->toDateString())
            ->count();

        $turnoutRate = $registeredVoters > 0
            ? round(($totalVoted / $registeredVoters) * 100, 1)
            : 0.0;

        return Inertia::render('dashboard', [
            'dashboardData' => [
                'overview' => [
                    'active_elections' => $activeElections,
                    'registered_voters' => $registeredVoters,
                    'votes_today' => $votesToday,
                    'turnout_rate' => $turnoutRate,
                    'total_elections' => $totalElections,
                    'organizations_count' => $organizationIds->count(),
                ],
                'pipeline' => $this->buildPipeline($organizationIds),
                'activity_feed' => $this->buildActivityFeed($organizationIds),
            ],
        ]);
    }

    private function buildPipeline(Collection $organizationIds): array
    {
        $stageMap = [
            'draft' => ['label' => 'Configuration', 'progress' => 25],
            'active' => ['label' => 'Live voting', 'progress' => 70],
            'stopped' => ['label' => 'Tabulation', 'progress' => 85],
            'closed' => ['label' => 'Audit review', 'progress' => 92],
            'published' => ['label' => 'Published', 'progress' => 100],
        ];

        return Election::query()
            ->whereIn('organization_id', $organizationIds)
            ->with('organization:id,name')
            ->withCount('voteSessions')
            ->latest('updated_at')
            ->limit(4)
            ->get()
            ->map(function (Election $election) use ($stageMap) {
                $mapped = $stageMap[$election->status] ?? ['label' => ucfirst($election->status), 'progress' => 0];

                return [
                    'name' => $election->title,
                    'stage' => $mapped['label'],
                    'progress' => $mapped['progress'],
                    'organization' => $election->organization?->name,
                    'votes_recorded' => $election->vote_sessions_count,
                ];
            })
            ->values()
            ->toArray();
    }

    private function buildActivityFeed(Collection $organizationIds): array
    {
        $sessionEvents = VoteSession::query()
            ->whereHas('election', fn ($query) => $query->whereIn('organization_id', $organizationIds))
            ->with('election:id,title')
            ->latest('submitted_at')
            ->limit(3)
            ->get()
            ->map(function (VoteSession $session) {
                $submittedAt = $session->submitted_at instanceof Carbon
                    ? $session->submitted_at
                    : Carbon::parse($session->submitted_at);

                return [
                    'title' => 'Ballot submitted for '.$session->election?->title,
                    'time' => $submittedAt->diffForHumans(),
                    'status' => 'info',
                    'sort_at' => $submittedAt->timestamp,
                ];
            });

        $electionEvents = Election::query()
            ->whereIn('organization_id', $organizationIds)
            ->latest('updated_at')
            ->limit(3)
            ->get(['id', 'title', 'status', 'updated_at'])
            ->map(function (Election $election) {
                $status = $election->status;
                $eventStatus = match ($status) {
                    'published' => 'success',
                    'active' => 'info',
                    'draft' => 'neutral',
                    default => 'warning',
                };

                return [
                    'title' => 'Election "'.$election->title.'" is now '.strtolower($status),
                    'time' => $election->updated_at?->diffForHumans() ?? now()->diffForHumans(),
                    'status' => $eventStatus,
                    'sort_at' => $election->updated_at?->timestamp ?? now()->timestamp,
                ];
            });

        return $sessionEvents
            ->concat($electionEvents)
            ->sortByDesc('sort_at')
            ->take(6)
            ->map(function (array $event) {
                unset($event['sort_at']);

                return $event;
            })
            ->values()
            ->toArray();
    }
}
