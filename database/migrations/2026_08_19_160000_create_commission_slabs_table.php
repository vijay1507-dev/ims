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
        Schema::create('commission_slabs', function (Blueprint $table) {
            $table->id();
            $table->decimal('achievement_amount', 15, 2);
            $table->decimal('achievement_percentage', 8, 2);
            $table->integer('points');
            $table->decimal('commission_percentage', 8, 2);
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
        });

        // Pre-populate with default slabs
        $slabs = [
            [
                'achievement_amount' => 500000.00,
                'achievement_percentage' => 100.00,
                'points' => 100,
                'commission_percentage' => 0.00,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'achievement_amount' => 800000.00,
                'achievement_percentage' => 160.00,
                'points' => 160,
                'commission_percentage' => 25.00,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'achievement_amount' => 1000000.00,
                'achievement_percentage' => 200.00,
                'points' => 200,
                'commission_percentage' => 50.00,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'achievement_amount' => 1500000.00,
                'achievement_percentage' => 300.00,
                'points' => 300,
                'commission_percentage' => 100.00,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'achievement_amount' => 2000000.00,
                'achievement_percentage' => 400.00,
                'points' => 400,
                'commission_percentage' => 150.00,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'achievement_amount' => 3000000.00,
                'achievement_percentage' => 600.00,
                'points' => 600,
                'commission_percentage' => 300.00,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'achievement_amount' => 4000000.00,
                'achievement_percentage' => 800.00,
                'points' => 800,
                'commission_percentage' => 500.00,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('commission_slabs')->insert($slabs);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_slabs');
    }
};
