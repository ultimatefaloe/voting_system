<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationMember;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationsPageController extends Controller
{
    public function __invoke(): Response
    {
        $user = request()->user();

        $memberships = OrganizationMember::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->with([
                'organization' => fn ($query) => $query
                    ->withCount([
                        'organizationMembers as members_count' => fn ($memberQuery) => $memberQuery->where('status', 'active'),
                        'invites as pending_invites_count' => fn ($inviteQuery) => $inviteQuery->where('status', 'pending'),
                        'elections as elections_count',
                    ]),
            ])
            ->get();

        $organizations = $memberships
            ->filter(fn (OrganizationMember $membership) => $membership->organization !== null)
            ->map(function (OrganizationMember $membership) {
                /** @var Organization $organization */
                $organization = $membership->organization;

                return [
                    'id' => $organization->id,
                    'name' => $organization->name,
                    'slug' => $organization->slug,
                    'role' => $membership->role,
                    'members_count' => $organization->members_count ?? 0,
                    'pending_invites_count' => $organization->pending_invites_count ?? 0,
                    'elections_count' => $organization->elections_count ?? 0,
                    'created_at' => $organization->created_at?->toDateString(),
                ];
            })
            ->values();

        return Inertia::render('organizations/index', [
            'organizationsData' => [
                'items' => $organizations,
                'summary' => [
                    'organizations_count' => $organizations->count(),
                    'active_memberships' => $memberships->count(),
                    'total_elections' => $organizations->sum('elections_count'),
                ],
            ],
        ]);
    }
}
