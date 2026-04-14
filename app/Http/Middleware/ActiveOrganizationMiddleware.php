<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActiveOrganizationMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            // Get active organization from header or session
            $orgId = $request->header('X-Organization-ID') 
                ?: session('active_organization_id');

            if ($orgId) {
                $organization = $request->user()
                    ->organizations()
                    ->find($orgId);

                if ($organization) {
                    $request->attributes->set('organization', $organization);
                    session(['active_organization_id' => $orgId]);
                }
            }
        }

        return $next($request);
    }
}
