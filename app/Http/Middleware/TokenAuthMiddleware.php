<?php

namespace App\Http\Middleware;

use App\Models\ElectionAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenAuthMiddleware
{
    /**
     * Handle an incoming request for voter authentication
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get voter token from header
        $token = $request->header('X-Voter-Token');

        if (!$token) {
            return response()->json([
                'message' => 'Voter token is required',
            ], 401);
        }

        $access = ElectionAccess::where('token', $token)->first();

        if (!$access || !$access->isValid()) {
            return response()->json([
                'message' => 'Invalid or expired voter token',
            ], 401);
        }

        // Attach access record using both keys for backward compatibility
        $request->attributes->set('voter_access', $access);
        $request->attributes->set('voter_token', $access);
        return $next($request);
    }
}
