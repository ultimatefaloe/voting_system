<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    /**
     * Handle registration request
     */
    public function store(RegisterRequest $request): JsonResponse
    {
        // Check if user already exists
        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'message' => 'User already exists',
                'errors' => ['email' => ['This email is already registered.']],
            ], 422);
        }

        // Create new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // Create API token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'token' => $token,
            'user' => new UserResource($user),
        ], 201);
    }
}
