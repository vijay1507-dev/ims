<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

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
            'settings.manage',
            'users.manage',
            'billing.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create roles and assign permissions
        $adminRole = Role::findOrCreate('Admin');
        $adminRole->givePermissionTo(Permission::all());

        // Create a default admin user if not exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );

        $admin->assignRole($adminRole);
    }
}
