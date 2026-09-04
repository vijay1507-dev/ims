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
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('month');
            $table->integer('year');
            $table->decimal('salary', 15, 2);
            $table->decimal('total_payments_closed', 15, 2);
            $table->decimal('achievement_percentage', 8, 2);
            $table->integer('points');
            $table->foreignId('commission_slab_id')->nullable()->constrained('commission_slabs')->onDelete('set null');
            $table->decimal('commission_percentage', 8, 2);
            $table->decimal('commission_amount', 15, 2);
            $table->date('calculation_date');
            $table->string('status')->default('draft'); // draft, calculated, approved, paid
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
