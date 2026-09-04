<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add deleted_at columns for soft deletes
        $tables = ['customers', 'subscriptions', 'payments', 'invoices', 'inventories'];
        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $tableCol) {
                    $tableCol->softDeletes();
                });
            }
        }

        // 2. Normalize existing string dates to Y-m-d format
        // Customers (joined_date)
        if (Schema::hasTable('customers')) {
            $customers = DB::table('customers')->get();
            foreach ($customers as $c) {
                if (!empty($c->joined_date)) {
                    $time = strtotime($c->joined_date);
                    if ($time) {
                        DB::table('customers')->where('id', $c->id)->update(['joined_date' => date('Y-m-d', $time)]);
                    }
                }
            }
        }

        // Subscriptions (start_date)
        if (Schema::hasTable('subscriptions')) {
            $subscriptions = DB::table('subscriptions')->get();
            foreach ($subscriptions as $s) {
                if (!empty($s->start_date)) {
                    $time = strtotime($s->start_date);
                    if ($time) {
                        DB::table('subscriptions')->where('id', $s->id)->update(['start_date' => date('Y-m-d', $time)]);
                    }
                }
            }
        }

        // Payments (payment_date)
        if (Schema::hasTable('payments')) {
            $payments = DB::table('payments')->get();
            foreach ($payments as $p) {
                if (!empty($p->payment_date)) {
                    $time = strtotime($p->payment_date);
                    if ($time) {
                        DB::table('payments')->where('id', $p->id)->update(['payment_date' => date('Y-m-d', $time)]);
                    }
                }
            }
        }

        // Invoices (invoice_date)
        if (Schema::hasTable('invoices')) {
            $invoices = DB::table('invoices')->get();
            foreach ($invoices as $i) {
                if (!empty($i->invoice_date)) {
                    $time = strtotime($i->invoice_date);
                    if ($time) {
                        DB::table('invoices')->where('id', $i->id)->update(['invoice_date' => date('Y-m-d', $time)]);
                    }
                }
            }
        }

        // Inventories (assigned_date)
        if (Schema::hasTable('inventories')) {
            $inventories = DB::table('inventories')->get();
            foreach ($inventories as $inv) {
                if (!empty($inv->assigned_date)) {
                    $time = strtotime($inv->assigned_date);
                    if ($time) {
                        DB::table('inventories')->where('id', $inv->id)->update(['assigned_date' => date('Y-m-d', $time)]);
                    }
                }
            }
        }

        // 3. Create Spatie permissions
        $bulkPermissions = [
            'bulk-delete-customers',
            'bulk-delete-subscriptions',
            'bulk-delete-payments',
            'bulk-delete-invoices',
            'bulk-delete-inventory',
        ];

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($bulkPermissions as $perm) {
            Permission::findOrCreate($perm);
        }

        try {
            $adminRole = Role::findByName('Admin');
            if ($adminRole) {
                foreach ($bulkPermissions as $perm) {
                    $adminRole->givePermissionTo($perm);
                }
            }
        } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
            // Role doesn't exist yet during fresh migration setup
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['customers', 'subscriptions', 'payments', 'invoices', 'inventories'];
        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $tableCol) {
                    $tableCol->dropSoftDeletes();
                });
            }
        }

        $bulkPermissions = [
            'bulk-delete-customers',
            'bulk-delete-subscriptions',
            'bulk-delete-payments',
            'bulk-delete-invoices',
            'bulk-delete-inventory',
        ];

        foreach ($bulkPermissions as $perm) {
            $permission = Permission::findByName($perm);
            if ($permission) {
                $permission->delete();
            }
        }
    }
};
