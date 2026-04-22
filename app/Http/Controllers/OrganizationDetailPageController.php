<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationDetailPageController extends Controller
{
    public function __invoke(Organization $organization): Response
    {
        $this->authorize('view', $organization);

        $organization->loadCount([
            'organizationMembers as active_members_count' => fn ($query) => $query->where('status', 'active'),
            'invites as pending_invites_count' => fn ($query) => $query->where('status', 'pending'),
            'elections',
        ]);

        $members = $organization->organizationMembers()
            ->with('user:id,name,email')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($membership) {
                return [
                    'id' => $membership->id,
                    'user_id' => $membership->user_id,
                    'name' => $membership->user?->name,
                    'email' => $membership->user?->email,
                    'role' => $membership->role,
                    'status' => $membership->status,
                    'created_at' => $membership->created_at?->toDateString(),
                ];
            })
            ->values();

        $invites = $organization->invites()
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($invite) {
                return [
                    'id' => $invite->id,
                    'email' => $invite->email,
                    'role' => $invite->role,
                    'status' => $invite->status,
                    'expires_at' => $invite->expires_at?->toDateString(),
                    'created_at' => $invite->created_at?->toDateString(),
                ];
            })
            ->values();

        return Inertia::render('organizations/show', [
            'organizationData' => [
                'organization' => [
                    'id' => $organization->id,
                    'name' => $organization->name,
                    'slug' => $organization->slug,
                    'owner_id' => $organization->owner_id,
                ],
                'summary' => [
                    'active_members_count' => $organization->active_members_count,
                    'pending_invites_count' => $organization->pending_invites_count,
                    'elections_count' => $organization->elections_count,
                ],
                'members' => $members,
                'invites' => $invites,
                'permissions' => [
                    'can_manage_members' => Auth::user() ? Auth::user()->can('manageMember', $organization) : false,
                    'current_user_role' => Auth::user() ? $organization->getUserRole(Auth::user()) : null,
                ],
            ],
        ]);
    }
}
