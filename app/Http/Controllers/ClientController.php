<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Customer;
use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Validation\Rules\Password;

class ClientController extends Controller
{
    /**
     * Enforce strict Superadmin role requirement.
     */
    private function authorizeSuperadmin(): void
    {
        if (!auth()->user() || !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized. Client Records are accessible only by Superadmin.');
        }
    }

    /**
     * Display a listing of clients / tenants.
     */
    public function index(Request $request)
    {
        $this->authorizeSuperadmin();
        $search = $request->query('search');

        $query = Tenant::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('data', 'like', "%{$search}%");
            });
        }

        $tenants = $query->latest()->get()->map(function ($tenant) {
            $domainRecord = Domain::where('tenant_id', $tenant->id)->first();
            $primaryDomain = $domainRecord?->domain ?? 'N/A';
            $isDomainActive = (bool)($domainRecord?->is_active ?? true);
            $primaryUser = User::withoutGlobalScopes()->where('tenant_id', $tenant->id)->first();
            $usersCount = User::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count();
            $customersCount = Customer::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count();

            return [
                'id' => $tenant->id,
                'name' => $tenant->name ?? 'Organization #' . $tenant->id,
                'domain' => $primaryDomain,
                'is_active' => $isDomainActive,
                'admin_name' => $primaryUser?->name ?? 'N/A',
                'admin_email' => $primaryUser?->email ?? 'N/A',
                'users_count' => $usersCount,
                'customers_count' => $customersCount,
                'created_at' => $tenant->created_at ? $tenant->created_at->format('M d, Y') : 'N/A',
            ];
        });

        $metrics = [
            'total_clients' => Tenant::count(),
            'active_domains' => Domain::where('is_active', true)->count(),
            'total_users' => User::withoutGlobalScopes()->whereNotNull('tenant_id')->count(),
            'total_customers' => Customer::withoutGlobalScopes()->count(),
        ];

        return Inertia::render('Clients/Index', [
            'clients' => $tenants,
            'metrics' => $metrics,
            'filters' => [
                'search' => $search ?? '',
            ],
        ]);
    }

    /**
     * Store a newly created client / tenant in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeSuperadmin();
        $subdomain = strtolower(trim($request->subdomain ?? ''));
        $centralDomains = config('tenancy.central_domains', []);
        $appHost = reset($centralDomains) ?: (parse_url(config('app.url'), PHP_URL_HOST) ?: '');
        $domainName = str_contains($subdomain, '.') ? $subdomain : ($appHost ? ($subdomain . '.' . $appHost) : $subdomain);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subdomain' => [
                'required',
                'string',
                'alpha_dash',
                'max:50',
                function ($attribute, $value, $fail) use ($domainName, $centralDomains) {
                    if (in_array(strtolower($value), $centralDomains) || in_array(strtolower($domainName), $centralDomains)) {
                        $fail('The sub-domain cannot be a central domain.');
                    }
                    if (Domain::where('domain', $domainName)->orWhere('domain', strtolower($value))->exists()) {
                        $fail('The sub-domain name has already been taken.');
                    }
                },
            ],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'admin_password' => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'name.required' => 'The company / organization name field is required.',
            'subdomain.required' => 'The domain name field is required.',
            'admin_name.required' => 'The admin name field is required.',
            'admin_email.required' => 'The admin email field is required.',
            'admin_password.required' => 'The password field is required.',
        ]);

        // 1. Create Tenant
        $tenant = Tenant::create([
            'name' => $request->name,
        ]);

        // 2. Create Domain mapping
        Domain::create([
            'domain' => $domainName,
            'tenant_id' => $tenant->id,
        ]);

        // 3. Create Primary Admin User for Tenant
        $user = User::create([
            'name' => $request->admin_name,
            'email' => $request->admin_email,
            'password' => Hash::make($request->admin_password),
            'tenant_id' => $tenant->id,
        ]);

        $user->assignRole('Admin');

        return redirect()->back()->with('success', "Client tenant '{$request->name}' created successfully!");
    }

    /**
     * Remove the specified client / tenant from storage.
     */
    public function destroy(Tenant $client)
    {
        $this->authorizeSuperadmin();
        Domain::where('tenant_id', $client->id)->delete();
        $client->delete();

        return redirect()->back()->with('success', 'Client tenant deleted successfully!');
    }

    /**
     * Toggle domain enabled / disabled status.
     */
    public function toggleDomain(Tenant $client)
    {
        $this->authorizeSuperadmin();

        $domain = Domain::where('tenant_id', $client->id)->first();

        if (!$domain) {
            return redirect()->back()->with('error', 'No domain record found for this client.');
        }

        $domain->is_active = !$domain->is_active;
        $domain->save();

        $statusText = $domain->is_active ? 'enabled' : 'disabled';
        return redirect()->back()->with('success', "Domain '{$domain->domain}' has been {$statusText}.");
    }
}
