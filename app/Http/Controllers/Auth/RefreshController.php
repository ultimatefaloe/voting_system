<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RefreshController extends Controller
{
    /**
     * Refresh authentication token
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Delete old token
        $user->tokens()->delete();

        // Create new token
        $newToken = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Token refreshed successfully',
            'token' => $newToken,
        ], 200);
    }
}
