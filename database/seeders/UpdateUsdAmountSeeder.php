<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateUsdAmountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update payments where usd_amount is 0 to match amount and ensure currency is USD
        DB::table('payments')
            ->where('usd_amount', 0)
            ->update([
                'usd_amount' => DB::raw('amount'),
                'currency' => 'USD'
            ]);

        // Update invoices where usd_amount is 0 to match amount and ensure currency is USD
        DB::table('invoices')
            ->where('usd_amount', 0)
            ->update([
                'usd_amount' => DB::raw('amount'),
                'currency' => 'USD'
            ]);
    }
}
