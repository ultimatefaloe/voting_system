<?php

namespace App\Http\Controllers;

use App\Http\Resources\MemberResource;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Http\Requests\Organization\StoreMemberRequest;
use App\Http\Requests\Organization\UpdateMemberRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    /**
     * Display all members of an organization.
     *
     * @param Organization $organization
     * @return JsonResponse
     */
    public function index(Organization $organization): JsonResponse
    {
        $this->authorize('view', $organization);

        $members = $organization->members()->get();

        return response()->json([
            'message' => 'Organization members retrieved successfully',
            'data' => MemberResource::collection($members),
        ]);
    }

    /**
     * Add a member to an organization.
     *
     * @param StoreMemberRequest $request
     * @param Organization $organization
     * @return JsonResponse
     */
    public function store(StoreMemberRequest $request, Organization $organization): JsonResponse
    {
        $this->authorize('manageMember', $organization);

        try {
            // Check if user is already a member
            if ($organization->hasMember($request->user_id)) {
                return response()->json([
                    'message' => 'User is already a member of this organization',
                ], 409);
            }

            // Prevent adding user as owner (only the organization owner can be owner)
            if ($request->role === 'owner') {
                return response()->json([
                    'message' => 'Cannot add members with owner role',
                ], 400);
            }

            $organization->members()->attach($request->user_id, [
                'role' => $request->role,
                'status' => 'active',
            ]);

            $user = $organization->members()->where('user_id', $request->user_id)->first();

            return response()->json([
                'message' => 'Member added successfully',
                'data' => new MemberResource($user),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add member',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a member's role.
     *
     * @param UpdateMemberRequest $request
     * @param Organization $organization
     * @param int $userId
     * @return JsonResponse
     */
    public function update(UpdateMemberRequest $request, Organization $organization, int $userId): JsonResponse
    {
        $this->authorize('manageMember', $organization);

        try {
            $member = $organization->members()->where('user_id', $userId)->first();

            if (!$member) {
                return response()->json([
                    'message' => 'User is not a member of this organization',
                ], 404);
            }

            // Prevent changing owner role
            if ($member->pivot->role === 'owner' && $request->role !== 'owner') {
                return response()->json([
                    'message' => 'Cannot change the role of the organization owner',
                ], 403);
            }

            // Prevent adding owner role to regular members
            if ($request->role === 'owner') {
                return response()->json([
                    'message' => 'Cannot promote members to owner role',
                ], 400);
            }

            $member->pivot->update(['role' => $request->role]);

            return response()->json([
                'message' => 'Member role updated successfully',
                'data' => new MemberResource($member),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update member role',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove a member from an organization.
     *
     * @param Organization $organization
     * @param int $userId
     * @return JsonResponse
     */
    public function destroy(Organization $organization, int $userId): JsonResponse
    {
        $this->authorize('manageMember', $organization);

        try {
            $member = $organization->members()->where('user_id', $userId)->first();

            if (!$member) {
                return response()->json([
                    'message' => 'User is not a member of this organization',
                ], 404);
            }

            // Prevent removing organization owner
            if ($member->pivot->role === 'owner') {
                return response()->json([
                    'message' => 'Cannot remove the organization owner',
                ], 403);
            }

            $organization->members()->detach($userId);

            return response()->json([
                'message' => 'Member removed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove member',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
