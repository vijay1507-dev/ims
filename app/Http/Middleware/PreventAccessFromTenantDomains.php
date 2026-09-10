<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventAccessFromTenantDomains
{
    /**
     * Handle an incoming request. Prevent access to central routes from tenant domains.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $centralDomains = config('tenancy.central_domains', []);

        if (!in_array($request->getHost(), $centralDomains)) {
            abort(404);
        }

        // Security: Tenant users (tenant_id !== null) must not access central platform routes
        if ($request->user() && $request->user()->tenant_id !== null) {
            abort(403, 'Unauthorized. Tenant users cannot access central platform routes.');
        }

        return $next($request);
    }
}
