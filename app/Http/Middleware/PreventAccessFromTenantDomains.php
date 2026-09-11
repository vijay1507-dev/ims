<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Support\Facades\URL;
use \Illuminate\Support\Facades\Auth;

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

        // Security & UX: Tenant users (tenant_id !== null) must not access central platform routes.
        // Redirect them directly to their own tenant workspace dashboard.
        if ($request->user() && $request->user()->tenant_id !== null) {
            $domainRecord = Domain::where('tenant_id', $request->user()->tenant_id)->first();
            if ($domainRecord) {
                $scheme = $request->getScheme();
                $port = $request->getPort();
                $portSuffix = ($port && !in_array($port, [80, 443])) ? ":{$port}" : '';
                $rootUrl = "{$scheme}://{$domainRecord->domain}{$portSuffix}";

                URL::forceRootUrl($rootUrl);
                $targetUrl = URL::temporarySignedRoute(
                    'tenant.auto-login',
                    now()->addMinutes(10),
                    ['user' => $request->user()->id]
                );
                URL::forceRootUrl(null);

                Auth::guard('web')->logout();

                return redirect()->away($targetUrl);
            }

            abort(403, 'Unauthorized. Tenant users cannot access central platform routes.');
        }

        return $next($request);
    }
}
