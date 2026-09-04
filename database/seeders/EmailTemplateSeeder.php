<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\EmailTemplate::create([
            'name' => 'Enterprise Welcome Sequencer',
            'description' => 'Dispatched on initial customer master file creation event',
            'subject' => 'Your secure dashboard credentials for #{{company_name}}',
            'body' => '<h1>Welcome to SaaS Manager!</h1><p>Hello {{contact_name}},</p><p>Your account for {{company_name}} has been created.</p>',
        ]);

        \App\Models\EmailTemplate::create([
            'name' => 'Invoice Ledger Dispatch',
            'description' => 'Monthly billing cycle settlement confirmation transmission',
            'subject' => 'Invoice ledger object #{{invoice_id}} is ready for viewing',
            'body' => '<p>Dear {{contact_name}},</p><p>Your invoice #{{invoice_id}} for the amount of {{total}} is ready.</p>',
        ]);

        \App\Models\EmailTemplate::create([
            'name' => 'Payment Gateway Handshake Failures',
            'description' => 'Urgent exception fallback notices mapping direct retry logic web links',
            'subject' => 'Action Required: Subscription charging transaction error encountered',
            'body' => '<p>Urgent: Your payment has failed. Please update your payment method.</p>',
        ]);
    }
}
