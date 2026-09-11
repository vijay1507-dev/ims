<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Stancl\Tenancy\Database\Models\Domain;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user && $user->tenant_id) {
            $domainRecord = Domain::where('tenant_id', $user->tenant_id)->first();

            if ($domainRecord) {
                $scheme = $request->getScheme();
                $port = $request->getPort();
                $portSuffix = ($port && !in_array($port, [80, 443])) ? ":{$port}" : '';
                $rootUrl = "{$scheme}://{$domainRecord->domain}{$portSuffix}";

                URL::forceRootUrl($rootUrl);
                $targetUrl = URL::temporarySignedRoute(
                    'tenant.auto-login',
                    now()->addMinutes(10),
                    ['user' => $user->id]
                );
                URL::forceRootUrl(null);

                // Log out of central domain session since user will be authenticated on tenant domain
                Auth::guard('web')->logout();

                if ($request->wantsJson() && !$request->inertia()) {
                    return new JsonResponse(['redirect_url' => $targetUrl], 201);
                }

                return Inertia::location($targetUrl);
            }
        }

        return $request->wantsJson()
            ? new JsonResponse('', 201)
            : redirect()->intended(config('fortify.home'));
    }
}
