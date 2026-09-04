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
        Schema::create('billing_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->integer('duration_months');
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
        });

        // Insert default cycles
        \DB::table('billing_cycles')->insert([
            ['name' => 'Monthly', 'code' => 'monthly', 'duration_months' => 1, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Annual', 'code' => 'annual', 'duration_months' => 12, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '2 Months', 'code' => 'two_months', 'duration_months' => 2, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Half Yearly', 'code' => 'half_yearly', 'duration_months' => 6, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Quarterly', 'code' => 'quarterly', 'duration_months' => 3, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Evently', 'code' => 'evently', 'duration_months' => 1, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_cycles');
    }
};
