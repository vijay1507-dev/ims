<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Payment;
use App\Models\BillingCycle;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Populate end_date for existing payments
        // Populate end_date for existing payments using DB query to bypass Eloquent scopes/traits
        DB::table('payments')->whereNull('end_date')->orWhere('end_date', '')->orderBy('id')->chunk(100, function ($payments) {
            foreach ($payments as $payment) {
                $billingCycle = $payment->billing_cycle ?: 'monthly';
                $startDate = $payment->payment_date ?: ($payment->created_at ? date('Y-m-d', strtotime($payment->created_at)) : date('Y-m-d'));
                $ts = strtotime($startDate);
                if ($ts !== false) {
                    $cycle = DB::table('billing_cycles')->where('code', $billingCycle)->first();
                    $months = $cycle ? $cycle->duration_months : 1;
                    DB::table('payments')->where('id', $payment->id)->update([
                        'end_date' => date('M d, Y', strtotime("+{$months} months", $ts))
                    ]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No action needed to rollback data population
    }
};
