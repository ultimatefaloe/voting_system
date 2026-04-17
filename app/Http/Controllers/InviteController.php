<?php

namespace App\Http\Controllers;

use App\Http\Resources\InviteResource;
use App\Models\Organization;
use App\Models\OrganizationInvite;
use App\Http\Requests\Organization\StoreInviteRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InviteController extends Controller
{
    /**
     * Display all pending invites for an organization.
     *
     * @param Organization $organization
     * @return JsonResponse
     */
    public function index(Organization $organization): JsonResponse
    {
        $this->authorize('manageMember', $organization);

        $invites = $organization->invites()->where('status', 'pending')->get();

        return response()->json([
            'message' => 'Organization invites retrieved successfully',
            'data' => InviteResource::collection($invites),
        ]);
    }

    /**
     * Send an invite to join an organization.
     *
     * @param StoreInviteRequest $request
     * @param Organization $organization
     * @return JsonResponse
     */
    public function store(StoreInviteRequest $request, Organization $organization): JsonResponse
    {
        $this->authorize('manageMember', $organization);

        try {
            // Check if email is already a member
            $existingMember = $organization->members()
                ->where('email', $request->email)
                ->first();

            if ($existingMember) {
                return response()->json([
                    'message' => 'This user is already a member of the organization',
                ], 409);
            }

            // Check if there's already a pending invite for this email
            $existingInvite = $organization->invites()
                ->where('email', $request->email)
                ->where('status', 'pending')
                ->first();

            if ($existingInvite) {
                return response()->json([
                    'message' => 'An invitation has already been sent to this email',
                ], 409);
            }

            $invite = $organization->invites()->create([
                'email' => $request->email,
                'role' => $request->role,
                'token' => Str::random(64),
                'expires_at' => $request->expires_at ?? now()->addDays(7),
                'invited_by' => Auth::id(),
                'status' => 'pending',
            ]);

            return response()->json([
                'message' => 'Invitation sent successfully',
                'data' => new InviteResource($invite),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send invitation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Accept an organization invite (public endpoint - no auth required on token).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function accept(Request $request): JsonResponse
    {
        $token = $request->input('token') ?? $request->header('X-Invite-Token');

        if (!$token) {
            return response()->json([
                'message' => 'Invite token is required',
            ], 400);
        }

        try {
            $invite = OrganizationInvite::where('token', $token)->first();

            if (!$invite) {
                return response()->json([
                    'message' => 'Invalid invite token',
                ], 404);
            }

            if ($invite->status !== 'pending') {
                return response()->json([
                    'message' => 'This invitation has already been accepted or rejected',
                ], 410);
            }

            if ($invite->expires_at && $invite->expires_at->isPast()) {
                return response()->json([
                    'message' => 'This invitation has expired',
                ], 410);
            }

            // Require user authentication to accept
            if (!Auth::check()) {
                return response()->json([
                    'message' => 'You must be logged in to accept an invitation',
                ], 401);
            }

            // Check if authenticated user email matches invite email
            if (Auth::user()->email !== $invite->email) {
                return response()->json([
                    'message' => 'You cannot accept an invitation for another email address',
                ], 403);
            }

            // Add user to organization
            $organization = $invite->organization;
            $organization->members()->attach(Auth::id(), [
                'role' => $invite->role,
                'status' => 'active',
            ]);

            // Mark invite as accepted
            $invite->update(['status' => 'accepted']);

            return response()->json([
                'message' => 'Invitation accepted successfully',
                'data' => [
                    'organization' => [
                        'id' => $organization->id,
                        'name' => $organization->name,
                        'slug' => $organization->slug,
                        'description' => $organization->description,
                    ],
                    'role' => $invite->role,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to accept invitation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject an organization invite.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reject(Request $request): JsonResponse
    {
        $token = $request->input('token') ?? $request->header('X-Invite-Token');

        if (!$token) {
            return response()->json([
                'message' => 'Invite token is required',
            ], 400);
        }

        try {
            $invite = OrganizationInvite::where('token', $token)->first();

            if (!$invite) {
                return response()->json([
                    'message' => 'Invalid invite token',
                ], 404);
            }

            if ($invite->status !== 'pending') {
                return response()->json([
                    'message' => 'This invitation is no longer pending',
                ], 410);
            }

            // Model rejection by removing the pending invite instead of
            // writing an unsupported enum value to the status column.
            $invite->delete();

            return response()->json([
                'message' => 'Invitation rejected successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to reject invitation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resend an invitation.
     *
     * @param Organization $organization
     * @param int $inviteId
     * @return JsonResponse
     */
    public function resend(Organization $organization, int $inviteId): JsonResponse
    {
        $this->authorize('manageMember', $organization);

        try {
            $invite = $organization->invites()->find($inviteId);

            if (!$invite) {
                return response()->json([
                    'message' => 'Invite not found',
                ], 404);
            }

            if ($invite->status !== 'pending') {
                return response()->json([
                    'message' => 'Only pending invites can be resent',
                ], 400);
            }

            // Generate new token
            $invite->update([
                'token' => Str::random(64),
                'expires_at' => now()->addDays(7),
            ]);

            return response()->json([
                'message' => 'Invitation resent successfully',
                'data' => [
                    'id' => $invite->id,
                    'email' => $invite->email,
                    'role' => $invite->role,
                    'token' => $invite->token,
                    'expires_at' => $invite->expires_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to resend invitation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel an invitation.
     *
     * @param Organization $organization
     * @param int $inviteId
     * @return JsonResponse
     */
    public function cancel(Organization $organization, int $inviteId): JsonResponse
    {
        $this->authorize('manageMember', $organization);

        try {
            $invite = $organization->invites()->find($inviteId);

            if (!$invite) {
                return response()->json([
                    'message' => 'Invite not found',
                ], 404);
            }

            if ($invite->status !== 'pending') {
                return response()->json([
                    'message' => 'Only pending invites can be cancelled',
                ], 400);
            }

            $invite->delete();

            return response()->json([
                'message' => 'Invitation cancelled successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to cancel invitation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
