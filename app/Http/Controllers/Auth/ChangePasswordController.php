<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use Illuminate\Http\JsonResponse;

class ChangePasswordController extends Controller
{
    /**
     * Change authenticated user's password
     */
    public function store(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        // Update password
        $user->update([
            'password' => bcrypt($request->password),
        ]);

        return response()->json([
            'message' => 'Password changed successfully',
        ], 200);
    }
}
