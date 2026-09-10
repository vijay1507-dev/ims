<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Support\Facades\DB;

class BackfillDefaultTenantSeeder extends Seeder
{
    /**
     * Seed initial tenant and backfill existing data.
     */
    public function run(): void
    {
        // 1. Create or get default tenant with numeric ID 1
        $tenant = Tenant::firstOrCreate(
            ['id' => 1],
            ['name' => 'Default Organization']
        );

        $defaultTenantId = $tenant->id;

        // 2. Assign tenant subdomain mapping (e.g. ims.localhost or ims.thevistiq.com)
        $centralDomains = config('tenancy.central_domains', ['localhost']);
        $baseHost = reset($centralDomains) ?: 'localhost';
        $tenantSubdomain = 'ims.' . $baseHost;

        Domain::firstOrCreate(
            ['domain' => $tenantSubdomain],
            ['tenant_id' => $defaultTenantId]
        );

        // 3. Clean up any central domain entries mistakenly stored in domains table
        Domain::whereIn('domain', $centralDomains)
            ->orWhereIn('domain', ['localhost', '127.0.0.1'])
            ->delete();

        // 4. Backfill all existing null tenant_id records to 1 (except central users / superadmin)
        $superadminUserIds = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'superadmin')
            ->where('model_has_roles.model_type', \App\Models\User::class)
            ->pluck('model_has_roles.model_id')
            ->toArray();

        // Backfill users
        DB::table('users')
            ->whereNull('tenant_id')
            ->whereNotIn('id', $superadminUserIds)
            ->update(['tenant_id' => $defaultTenantId]);

        // Ensure superadmins remain central users (tenant_id = null)
        if (!empty($superadminUserIds)) {
            DB::table('users')
                ->whereIn('id', $superadminUserIds)
                ->update(['tenant_id' => null]);
        }

        $tables = [
            'customers',
            'sub_customers',
            'subscriptions',
            'invoices',
            'payments',
            'inventories',
            'expenses',
            'purchases',
            'purchase_items',
            'contracts',
            'contract_types',
            'billing_cycles',
            'payment_channels',
            'email_templates',
            'activity_logs',
            'commissions',
            'commission_slabs',
        ];

        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table) && DB::getSchemaBuilder()->hasColumn($table, 'tenant_id')) {
                DB::table($table)->whereNull('tenant_id')->update(['tenant_id' => $defaultTenantId]);
            }
        }
    }
}
