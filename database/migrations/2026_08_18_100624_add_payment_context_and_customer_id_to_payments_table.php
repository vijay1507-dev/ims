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
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->after('transaction_id');
            $table->string('payment_context')->default('new')->after('status');
        });

        // Populate existing payments with customer_id and payment_context
        $payments = DB::table('payments')->get();
        foreach ($payments as $p) {
            $customer = DB::table('customers')->where('name', $p->customer_name)->first();
            $customerId = $customer ? $customer->id : null;

            // Determine if renewal:
            $isRenewal = false;
            if ($p->status === 'successful') {
                $prevCount = DB::table('payments')
                    ->where('customer_name', $p->customer_name)
                    ->where('status', 'successful')
                    ->where('id', '<', $p->id)
                    ->count();
                if ($prevCount > 0) {
                    $isRenewal = true;
                }
            }

            if ($p->invoice_ref && strpos($p->invoice_ref, 'REN') !== false) {
                $isRenewal = true;
            }

            DB::table('payments')->where('id', $p->id)->update([
                'customer_id' => $customerId,
                'payment_context' => $isRenewal ? 'renewal' : 'new'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['customer_id', 'payment_context']);
        });
    }
};
