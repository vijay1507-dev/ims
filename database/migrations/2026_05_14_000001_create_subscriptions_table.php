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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_avatar')->nullable();
            $table->string('plan_name')->default('Professional Plan');
            $table->string('billing_cycle')->default('monthly'); // monthly, annual
            $table->string('next_billing_date')->nullable();
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->string('status')->default('active'); // active, trial, paused, cancelled
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
