<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * List of tables to apply tenant_id.
     */
    protected array $tenantTables = [
        'users',
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
        'renewals',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tenantTables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('tenant_id')->nullable()->index();
                    $table->foreign('tenant_id')
                          ->references('id')
                          ->on('tenants')
                          ->onUpdate('cascade')
                          ->onDelete('cascade');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tenantTables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['tenant_id']);
                    $table->dropColumn('tenant_id');
                });
            }
        }
    }
};
