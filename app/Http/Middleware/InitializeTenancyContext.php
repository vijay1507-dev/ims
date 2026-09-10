<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Stancl\Tenancy\Database\Models\Domain;

class InitializeTenancyContext
{
    /**
     * Handle an incoming request dynamically using the database domains table.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!tenancy()->initialized) {
            $host = $request->getHost(); // Extract host without port (e.g. 'ims.localhost', 'localhost', '127.0.0.1')
            $centralDomains = config('tenancy.central_domains', []);

            // Central domain requests remain in central context
            if (in_array($host, $centralDomains)) {
                return $next($request);
            }

            // 1. Dynamic DB lookup in the domains table for tenant subdomains
            $domainRecord = Domain::where('domain', $host)->first();

            if ($domainRecord) {
                if (isset($domainRecord->is_active) && !$domainRecord->is_active) {
                    abort(403, 'This workspace domain has been disabled by the system administrator.');
                }
                tenancy()->initialize($domainRecord->tenant_id);
            } else {
                abort(404, 'Domain not found. This domain is not configured for any tenant.');
            }
        }

        return $next($request);
    }
}
