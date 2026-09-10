<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('renewals') && !Schema::hasColumn('renewals', 'tenant_id')) {
            Schema::table('renewals', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id')->index();
                $table->foreign('tenant_id')
                      ->references('id')
                      ->on('tenants')
                      ->onUpdate('cascade')
                      ->onDelete('cascade');
            });

            // Backfill existing renewals to default tenant if available
            $defaultTenant = DB::table('tenants')->first();
            if ($defaultTenant) {
                DB::table('renewals')->whereNull('tenant_id')->update(['tenant_id' => $defaultTenant->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('renewals') && Schema::hasColumn('renewals', 'tenant_id')) {
            Schema::table('renewals', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};
