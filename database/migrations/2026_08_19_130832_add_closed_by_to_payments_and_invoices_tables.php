<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('closed_by')->nullable()->after('sub_customer_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('closed_by')->nullable()->after('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('closed_by');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('closed_by');
        });
    }
};
