<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\BillingCycle;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'isCentralDomain' => in_array($request->getHost(), config('tenancy.central_domains', [])),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'roles' => $request->user()->getRoleNames(),
                    'role' => $request->user()->role,
                    'is_superadmin' => $request->user()->isSuperAdmin(),
                    'permissions' => ($request->user()->isSuperAdmin() || $request->user()->hasRole('Admin') || $request->user()->hasRole(2))
                        ? \Spatie\Permission\Models\Permission::pluck('name')
                        : $request->user()->getAllPermissions()->pluck('name'),
                ] : null,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'activeBillingCycles' => \Schema::hasTable('billing_cycles')
                ? BillingCycle::where('status', 'active')->orderBy('duration_months')->get()
                : [],
        ]);
    }
}
