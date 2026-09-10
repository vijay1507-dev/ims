<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

use App\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        $subdomain = strtolower(trim($input['subdomain'] ?? ''));
        $centralDomains = config('tenancy.central_domains', []);
        $appHost = reset($centralDomains) ?: (parse_url(config('app.url'), PHP_URL_HOST) ?: '');
        $domainName = str_contains($subdomain, '.') ? $subdomain : ($appHost ? ($subdomain . '.' . $appHost) : $subdomain);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'company_name' => ['nullable', 'string', 'max:255'],
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
        ])->validate();

        $companyName = !empty($input['company_name']) ? $input['company_name'] : ($input['name'] . ' Organization');

        // Create Tenant
        $tenant = Tenant::create([
            'name' => $companyName,
        ]);

        // Create Domain associated with tenant
        Domain::create([
            'domain' => $domainName,
            'tenant_id' => $tenant->id,
        ]);

        // Create primary admin user for this tenant
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'tenant_id' => $tenant->id,
        ]);

        // Assign 'Admin' role to the newly created user
        $user->assignRole('Admin');

        return $user;
    }
}
