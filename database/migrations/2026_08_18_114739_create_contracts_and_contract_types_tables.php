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
        Schema::create('contract_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique();
            $table->string('subject');
            $table->decimal('value', 15, 2)->default(0.00);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('pending'); // pending, active, expired, terminated
            $table->unsignedBigInteger('contract_type_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('contract_type_id')->references('id')->on('contract_types')->onDelete('restrict');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
        });

        // Seed initial permissions
        $perms = [
            'contracts.view',
            'contracts.create',
            'contracts.edit',
            'contracts.delete',
            'contract_types.view',
            'contract_types.create',
            'contract_types.edit',
            'contract_types.delete',
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
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('contract_types');

        $perms = [
            'contracts.view',
            'contracts.create',
            'contracts.edit',
            'contracts.delete',
            'contract_types.view',
            'contract_types.create',
            'contract_types.edit',
            'contract_types.delete',
        ];

        foreach ($perms as $permName) {
            $permission = \Spatie\Permission\Models\Permission::where('name', $permName)->first();
            if ($permission) {
                $permission->delete();
            }
        }
    }
};
