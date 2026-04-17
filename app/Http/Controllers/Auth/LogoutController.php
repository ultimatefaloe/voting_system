<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    /**
     * Handle logout request
     */
    public function destroy(Request $request): JsonResponse
    {
        // Revoke current token
        $token = $request->user()->currentAccessToken();
        if ($token instanceof \Laravel\Sanctum\PersonalAccessToken || (is_object($token) && method_exists($token, 'delete'))) {
            // Delete token via DB to avoid undefined method issues for certain token objects
            if (isset($token->id)) {
                \Illuminate\Support\Facades\DB::table('personal_access_tokens')->where('id', $token->id)->delete();
            }
        }

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }
}
