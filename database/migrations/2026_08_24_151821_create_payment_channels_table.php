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
        Schema::create('payment_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Seed initial payment channels
        $channels = [
            ['name' => 'Visa Gateway', 'code' => 'visa', 'status' => 'active'],
            ['name' => 'MasterCard Provider', 'code' => 'mastercard', 'status' => 'active'],
            ['name' => 'Stripe Connect', 'code' => 'stripe', 'status' => 'active'],
            ['name' => 'PayPal Express', 'code' => 'paypal', 'status' => 'active'],
        ];

        foreach ($channels as $channel) {
            DB::table('payment_channels')->insert(array_merge($channel, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Seed Spatie permissions
        $perms = [
            'payment_channels.view',
            'payment_channels.create',
            'payment_channels.edit',
            'payment_channels.delete',
        ];

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($perms as $permName) {
            \Spatie\Permission\Models\Permission::findOrCreate($permName);
        }

        $adminRole = \Spatie\Permission\Models\Role::where('name', 'Admin')->first();
        if ($adminRole) {
            foreach ($perms as $permName) {
                $adminRole->givePermissionTo($permName);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_channels');

        $perms = [
            'payment_channels.view',
            'payment_channels.create',
            'payment_channels.edit',
            'payment_channels.delete',
        ];

        foreach ($perms as $permName) {
            $permission = \Spatie\Permission\Models\Permission::where('name', $permName)->first();
            if ($permission) {
                $permission->delete();
            }
        }
    }
};
