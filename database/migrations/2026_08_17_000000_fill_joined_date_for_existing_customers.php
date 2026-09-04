<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Customer;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Normalize existing customer joined_date fields to Y-m-d format
        // Normalize existing customer joined_date fields using DB queries to bypass Eloquent scopes/traits
        foreach (DB::table('customers')->get() as $customer) {
            if (empty($customer->joined_date)) {
                $joined_date = $customer->created_at ? date('Y-m-d', strtotime($customer->created_at)) : date('Y-m-d');
            } else {
                $time = strtotime($customer->joined_date);
                if ($time) {
                    $joined_date = date('Y-m-d', $time);
                } else {
                    $joined_date = $customer->created_at ? date('Y-m-d', strtotime($customer->created_at)) : date('Y-m-d');
                }
            }
            DB::table('customers')->where('id', $customer->id)->update(['joined_date' => $joined_date]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op or restore to a readable format if needed, but Y-m-d is fine.
    }
};
