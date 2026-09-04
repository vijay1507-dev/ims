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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('asset_id')->unique();
            $table->string('category')->default('desktop'); // desktop, display, mobile, license, printer
            $table->string('type_name')->default('Desktop');
            $table->string('name_model');
            $table->string('customer_name');
            $table->string('serial_license')->nullable();
            $table->string('assigned_date')->nullable();
            $table->string('warranty_expiry')->nullable();
            $table->string('status')->default('active'); // active, maintenance, inactive, retired
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
