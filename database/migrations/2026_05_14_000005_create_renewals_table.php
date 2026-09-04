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
        Schema::create('renewals', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('type')->default('subscription'); // subscription, warranty, license
            $table->string('plan_asset');
            $table->string('current_value')->nullable();
            $table->string('renewal_date')->nullable();
            $table->string('days_left')->nullable();
            $table->string('priority')->default('normal'); // urgent, soon, normal, renewed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('renewals');
    }
};
