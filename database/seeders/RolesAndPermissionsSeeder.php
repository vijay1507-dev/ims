<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'dashboard.view',
            'reports.view',
            'customers.view',
            'customers.create',
            'customers.edit',
            'customers.delete',
            'inventory.view',
            'inventory.create',
            'inventory.edit',
            'inventory.delete',
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.delete',
            'subscriptions.view',
            'subscriptions.create',
            'subscriptions.edit',
            'subscriptions.delete',
            'payments.view',
            'payments.create',
            'payments.edit',
            'payments.delete',
            'renewals.view',
            'renewals.create',
            'renewals.edit',
            'renewals.delete',
            'expenses.view',
            'expenses.create',
            'expenses.edit',
            'expenses.delete',
            'purchases.view',
            'purchases.create',
            'purchases.edit',
            'purchases.delete',
            'contracts.view',
            'contracts.create',
            'contracts.edit',
            'contracts.delete',
            'contract_types.view',
            'contract_types.create',
            'contract_types.edit',
            'contract_types.delete',
            'payment_channels.view',
            'payment_channels.create',
            'payment_channels.edit',
            'payment_channels.delete',
            'settings.manage',
            'users.manage',
            'billing.manage',
        ];

        // Clean up legacy client permissions if present in DB
        Permission::whereIn('name', ['clients.view', 'clients.create', 'clients.edit', 'clients.delete'])->delete();

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create roles and assign permissions
        $superadminRole = Role::findOrCreate('superadmin');
        $superadminRole->syncPermissions([]); // Superadmin bypasses checks via Gate::before

        $adminRole = Role::findOrCreate('Admin');
        $adminRole->givePermissionTo(Permission::all());

        $staffRole = Role::findOrCreate('Staff');
        $staffRole->givePermissionTo([
            'dashboard.view',
            'customers.view',
            'subscriptions.view',
            'payments.view',
            'invoices.view',
            'inventory.view',
            'renewals.view',
            'reports.view',
        ]);

        // Assign Superadmin role to superadmin user
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'tenant_id' => null,
            ]
        );
        $superadmin->update(['tenant_id' => null]);
        $superadmin->assignRole($superadminRole);

        // Assign Admin role to default users
        $testUser = User::where('email', 'test@example.com')->first();
        if ($testUser) {
            $testUser->assignRole($adminRole);
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'tenant_id' => 1,
            ]
        );
        $admin->assignRole($adminRole);

        $staff = User::firstOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Staff User',
                'password' => Hash::make('password'),
                'tenant_id' => 1,
            ]
        );
        $staff->assignRole($staffRole);
    }
}
