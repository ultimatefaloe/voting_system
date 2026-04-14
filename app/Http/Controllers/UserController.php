<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get user profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'main_role' => $user->main_role,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ], 200);
    }

    /**
     * Update user profile
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['sometimes', 'string', 'min:2', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $request->user()->id],
        ]);

        $user = $request->user();
        $user->update($request->only(['name', 'email']));

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'main_role' => $user->main_role,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ], 200);
    }

    /**
     * Get user's organizations
     */
    public function organizations(Request $request): JsonResponse
    {
        $organizations = $request->user()
            ->organizations()
            ->with('organizationMembers')
            ->get()
            ->map(function ($org) use ($request) {
                return [
                    'id' => $org->id,
                    'name' => $org->name,
                    'slug' => $org->slug,
                    'owner_id' => $org->owner_id,
                    'role' => $org->getUserRole($request->user()),
                    'members_count' => $org->members()->count(),
                    'created_at' => $org->created_at,
                    'updated_at' => $org->updated_at,
                ];
            });

        return response()->json([
            'organizations' => $organizations,
        ], 200);
    }
}
