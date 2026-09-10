<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantUser
{
    /**
     * Handle an incoming request. Ensure tenant context is active and user belongs to this tenant.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $centralDomains = config('tenancy.central_domains');

        // Central domain requests remain in central context
        if (in_array($request->getHost(), $centralDomains)) {
            return $next($request);
        }

        if (!tenancy()->initialized) {
            abort(404, 'Tenant workspace not found.');
        }

        $user = $request->user();

        if ($user) {
            $currentTenantId = tenancy()->tenant->id;

            // If a central user (tenant_id === null) or user from another tenant attempts to access:
            if ($user->tenant_id === null || (int)$user->tenant_id !== (int)$currentTenantId) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', 'Unauthorized. You do not have access to this workspace.');
            }
        }

        return $next($request);
    }
}
