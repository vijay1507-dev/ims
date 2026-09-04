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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->string('customer_name');
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->string('payment_method')->default('****4242');
            $table->string('method_type')->default('visa'); // visa, mastercard, stripe, paypal
            $table->string('status')->default('successful'); // successful, failed, pending, refunded
            $table->string('invoice_ref')->nullable();
            $table->string('payment_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
