<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    /**
     * Send password reset link
     */
    public function sendLink(ForgotPasswordRequest $request): JsonResponse
    {
        $response = Password::sendResetLink(
            $request->only('email')
        );

        if ($response === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Password reset link sent to your email',
            ], 200);
        }

        return response()->json([
            'message' => 'Failed to send reset link',
        ], 400);
    }

    /**
     * Reset password with token
     */
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $response = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->update([
                    'password' => bcrypt($password),
                ]);
            }
        );

        if ($response === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password reset successfully',
            ], 200);
        }

        return response()->json([
            'message' => 'Failed to reset password',
        ], 400);
    }
}
