<?php

namespace App\Http\Controllers;

use App\Models\Election;
use App\Models\OrganizationMember;
use Inertia\Inertia;
use Inertia\Response;

class ResultsPageController extends Controller
{
    public function __invoke(): Response
    {
        $user = request()->user();

        $organizationIds = OrganizationMember::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('organization_id');

        $electionsBase = Election::query()
            ->whereIn('organization_id', $organizationIds)
            ->whereIn('status', ['closed', 'published']);

        $items = (clone $electionsBase)
            ->with(['organization:id,name'])
            ->withCount(['positions', 'voteSessions', 'votes'])
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get()
            ->map(function (Election $election) {
                return [
                    'id' => $election->id,
                    'organization' => $election->organization?->name,
                    'title' => $election->title,
                    'status' => $election->status,
                    'type' => $election->type,
                    'positions_count' => $election->positions_count,
                    'vote_sessions_count' => $election->vote_sessions_count,
                    'votes_count' => $election->votes_count,
                    'start_date' => $election->start_date?->toDateString(),
                    'end_date' => $election->end_date?->toDateString(),
                ];
            })
            ->values();

        return Inertia::render('results/index', [
            'resultsData' => [
                'items' => $items,
                'summary' => [
                    'closed_or_published' => (clone $electionsBase)->count(),
                    'published' => (clone $electionsBase)->where('status', 'published')->count(),
                    'closed_pending_publish' => (clone $electionsBase)->where('status', 'closed')->count(),
                ],
            ],
        ]);
    }
}
