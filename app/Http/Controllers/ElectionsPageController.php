<?php

namespace App\Http\Controllers;

use App\Models\Election;
use App\Models\OrganizationMember;
use Inertia\Inertia;
use Inertia\Response;

class ElectionsPageController extends Controller
{
    public function __invoke(): Response
    {
        $user = request()->user();

        $organizationIds = OrganizationMember::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('organization_id');

        $electionsBase = Election::query()->whereIn('organization_id', $organizationIds);

        $elections = (clone $electionsBase)
            ->with(['organization:id,name'])
            ->withCount(['positions', 'voteSessions'])
            ->orderByDesc('updated_at')
            ->get()
            ->map(function (Election $election) {
                return [
                    'id' => $election->id,
                    'organization_id' => $election->organization_id,
                    'organization' => $election->organization?->name,
                    'title' => $election->title,
                    'type' => $election->type,
                    'status' => $election->status,
                    'start_date' => $election->start_date?->toDateString(),
                    'end_date' => $election->end_date?->toDateString(),
                    'positions_count' => $election->positions_count,
                    'vote_sessions_count' => $election->vote_sessions_count,
                ];
            })
            ->values();

        return Inertia::render('elections/index', [
            'electionsData' => [
                'items' => $elections,
                'summary' => [
                    'total' => (clone $electionsBase)->count(),
                    'draft' => (clone $electionsBase)->where('status', 'draft')->count(),
                    'active' => (clone $electionsBase)->where('status', 'active')->count(),
                    'published' => (clone $electionsBase)->where('status', 'published')->count(),
                ],
            ],
        ]);
    }
}
