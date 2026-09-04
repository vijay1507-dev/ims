<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Customer;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use App\Models\SubscriptionPricing;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Inventory;
use App\Models\Renewal;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'John Doe', 'password' => bcrypt('password')]
        );

        if (Customer::count() === 0) {
            $customers = [
                [
                    'name' => 'Acme Corp',
                    'email' => 'contact@acme.com',
                    'avatar' => 'https://picsum.photos/seed/acme/32/32.jpg',
                    'plan' => 'Enterprise Plan',
                    'status' => 'active',
                    'mrr' => 2450.00,
                    'joined_date' => 'Jan 12, 2024',
                ],
                [
                    'name' => 'Tech Solutions',
                    'email' => 'info@techsolutions.io',
                    'avatar' => 'https://picsum.photos/seed/tech/32/32.jpg',
                    'plan' => 'Professional Plan',
                    'status' => 'active',
                    'mrr' => 890.00,
                    'joined_date' => 'Feb 15, 2024',
                ],
                [
                    'name' => 'Global Industries',
                    'email' => 'billing@globalind.com',
                    'avatar' => 'https://picsum.photos/seed/global/32/32.jpg',
                    'plan' => 'Starter Plan',
                    'status' => 'trial',
                    'mrr' => 49.00,
                    'joined_date' => 'Mar 01, 2024',
                ],
                [
                    'name' => 'Innovate Labs',
                    'email' => 'admin@innovatelabs.net',
                    'avatar' => 'https://picsum.photos/seed/innovate/32/32.jpg',
                    'plan' => 'Professional Plan',
                    'status' => 'inactive',
                    'mrr' => 890.00,
                    'joined_date' => 'Apr 10, 2024',
                ],
            ];

            foreach ($customers as $c) {
                Customer::create($c);
            }
        }

        if (Subscription::count() === 0) {
            $subscriptions = [
                [
                    'customer_name' => 'Acme Corp',
                    'customer_email' => 'contact@acme.com',
                    'customer_avatar' => 'https://picsum.photos/seed/acme/32/32.jpg',
                    'plan_name' => 'Enterprise Plan',
                    'billing_cycle' => 'annual',
                    'next_billing_date' => 'Jan 12, 2025',
                    'amount' => 2450.00,
                    'status' => 'active',
                ],
                [
                    'customer_name' => 'Tech Solutions',
                    'customer_email' => 'info@techsolutions.io',
                    'customer_avatar' => 'https://picsum.photos/seed/tech/32/32.jpg',
                    'plan_name' => 'Professional Plan',
                    'billing_cycle' => 'monthly',
                    'next_billing_date' => 'Jun 15, 2024',
                    'amount' => 89.00,
                    'status' => 'active',
                ],
                [
                    'customer_name' => 'Global Industries',
                    'customer_email' => 'billing@globalind.com',
                    'customer_avatar' => 'https://picsum.photos/seed/global/32/32.jpg',
                    'plan_name' => 'Starter Plan',
                    'billing_cycle' => 'monthly',
                    'next_billing_date' => 'Jun 01, 2024',
                    'amount' => 49.00,
                    'status' => 'trial',
                ],
                [
                    'customer_name' => 'Innovate Labs',
                    'customer_email' => 'admin@innovatelabs.net',
                    'customer_avatar' => 'https://picsum.photos/seed/innovate/32/32.jpg',
                    'plan_name' => 'Professional Plan',
                    'billing_cycle' => 'monthly',
                    'next_billing_date' => 'Jul 10, 2024',
                    'amount' => 89.00,
                    'status' => 'paused',
                ],
            ];

            foreach ($subscriptions as $s) {
                Subscription::create($s);
            }
        }

        if (SubscriptionPackage::count() === 0) {
            $packages = [
                [
                    'name' => 'Starter Plan',
                    'code' => 'starter',
                    'subtitle' => 'Essential features for small teams',
                    'description' => 'Deploy core tracking features instantly.',
                    'sort_order' => 1,
                    'is_most_popular' => false,
                    'prices' => [
                        ['currency' => 'USD', 'billing_cycle' => 'monthly', 'price' => 49.00],
                        ['currency' => 'USD', 'billing_cycle' => 'annual', 'price' => 490.00],
                    ],
                ],
                [
                    'name' => 'Professional Plan',
                    'code' => 'pro',
                    'subtitle' => 'Advanced telemetry and priority queues',
                    'description' => 'Ideal for scaling enterprise environments.',
                    'sort_order' => 2,
                    'is_most_popular' => true,
                    'prices' => [
                        ['currency' => 'USD', 'billing_cycle' => 'monthly', 'price' => 89.00],
                        ['currency' => 'USD', 'billing_cycle' => 'annual', 'price' => 890.00],
                    ],
                ],
                [
                    'name' => 'Enterprise Plan',
                    'code' => 'enterprise',
                    'subtitle' => 'Unlimited SLA support and dedicated hosting',
                    'description' => 'Maximum control and operational security infrastructure.',
                    'sort_order' => 3,
                    'is_most_popular' => false,
                    'prices' => [
                        ['currency' => 'USD', 'billing_cycle' => 'monthly', 'price' => 249.00],
                        ['currency' => 'USD', 'billing_cycle' => 'annual', 'price' => 2490.00],
                    ],
                ],
            ];

            foreach ($packages as $pkgData) {
                $prices = $pkgData['prices'];
                unset($pkgData['prices']);

                $pkg = SubscriptionPackage::create($pkgData);

                foreach ($prices as $p) {
                    $pkg->pricing()->create($p);
                }
            }
        }

        if (Payment::count() === 0) {
            $payments = [
                [
                    'transaction_id' => '#TXN-2024-0515',
                    'customer_name' => 'Acme Corp',
                    'amount' => 2450.00,
                    'payment_method' => '****4242',
                    'method_type' => 'visa',
                    'status' => 'successful',
                    'invoice_ref' => 'INV-2024-051',
                    'payment_date' => 'May 15, 2024',
                ],
                [
                    'transaction_id' => '#TXN-2024-0514',
                    'customer_name' => 'Tech Solutions',
                    'amount' => 890.00,
                    'payment_method' => '****5555',
                    'method_type' => 'mastercard',
                    'status' => 'successful',
                    'invoice_ref' => 'INV-2024-050',
                    'payment_date' => 'May 14, 2024',
                ],
                [
                    'transaction_id' => '#TXN-2024-0513',
                    'customer_name' => 'Global Industries',
                    'amount' => 1250.00,
                    'payment_method' => 'Stripe',
                    'method_type' => 'stripe',
                    'status' => 'failed',
                    'invoice_ref' => 'INV-2024-049',
                    'payment_date' => 'May 13, 2024',
                ],
                [
                    'transaction_id' => '#TXN-2024-0512',
                    'customer_name' => 'Innovate Labs',
                    'amount' => 890.00,
                    'payment_method' => 'PayPal',
                    'method_type' => 'paypal',
                    'status' => 'successful',
                    'invoice_ref' => 'INV-2024-048',
                    'payment_date' => 'May 12, 2024',
                ],
                [
                    'transaction_id' => '#TXN-2024-0511',
                    'customer_name' => 'StartUp LLC',
                    'amount' => 450.00,
                    'payment_method' => '****7890',
                    'method_type' => 'visa',
                    'status' => 'refunded',
                    'invoice_ref' => 'INV-2024-047',
                    'payment_date' => 'May 11, 2024',
                ],
            ];

            foreach ($payments as $pay) {
                Payment::create($pay);
            }
        }

        if (Invoice::count() === 0) {
            $invoices = [
                [
                    'invoice_number' => 'INV-2024-051',
                    'customer_name' => 'Acme Corp',
                    'amount' => 2450.00,
                    'tax' => 245.00,
                    'total' => 2695.00,
                    'invoice_date' => 'May 15, 2024',
                    'due_date' => 'Jun 15, 2024',
                    'status' => 'paid',
                ],
                [
                    'invoice_number' => 'INV-2024-050',
                    'customer_name' => 'Tech Solutions',
                    'amount' => 890.00,
                    'tax' => 89.00,
                    'total' => 979.00,
                    'invoice_date' => 'May 14, 2024',
                    'due_date' => 'Jun 14, 2024',
                    'status' => 'paid',
                ],
                [
                    'invoice_number' => 'INV-2024-049',
                    'customer_name' => 'Global Industries',
                    'amount' => 1250.00,
                    'tax' => 125.00,
                    'total' => 1375.00,
                    'invoice_date' => 'May 13, 2024',
                    'due_date' => 'Jun 13, 2024',
                    'status' => 'pending',
                ],
                [
                    'invoice_number' => 'INV-2024-048',
                    'customer_name' => 'Innovate Labs',
                    'amount' => 890.00,
                    'tax' => 89.00,
                    'total' => 979.00,
                    'invoice_date' => 'May 12, 2024',
                    'due_date' => 'May 12, 2024',
                    'status' => 'overdue',
                ],
                [
                    'invoice_number' => 'INV-2024-047',
                    'customer_name' => 'StartUp LLC',
                    'amount' => 450.00,
                    'tax' => 45.00,
                    'total' => 495.00,
                    'invoice_date' => 'May 11, 2024',
                    'due_date' => 'May 11, 2024',
                    'status' => 'draft',
                ],
            ];

            foreach ($invoices as $inv) {
                Invoice::create($inv);
            }
        }

        if (Inventory::count() === 0) {
            $assets = [
                [
                    'asset_id' => 'AST-001',
                    'category' => 'desktop',
                    'type_name' => 'Desktop',
                    'name_model' => 'Dell OptiPlex 7090',
                    'customer_name' => 'Acme Corp',
                    'serial_license' => 'DC-001-ACME',
                    'assigned_date' => 'Jan 15, 2024',
                    'warranty_expiry' => 'Jan 15, 2026',
                    'status' => 'active',
                ],
                [
                    'asset_id' => 'AST-002',
                    'category' => 'display',
                    'type_name' => 'Display',
                    'name_model' => 'Samsung 55" Smart TV',
                    'customer_name' => 'Tech Solutions',
                    'serial_license' => 'TV-002-TECH',
                    'assigned_date' => 'Feb 1, 2024',
                    'warranty_expiry' => 'Feb 1, 2026',
                    'status' => 'active',
                ],
                [
                    'asset_id' => 'AST-003',
                    'category' => 'mobile',
                    'type_name' => 'Tablet',
                    'name_model' => 'iPad Pro 12.9"',
                    'customer_name' => 'StartUp LLC',
                    'serial_license' => 'TB-003-START',
                    'assigned_date' => 'Mar 10, 2024',
                    'warranty_expiry' => 'Mar 10, 2026',
                    'status' => 'maintenance',
                ],
                [
                    'asset_id' => 'AST-004',
                    'category' => 'printer',
                    'type_name' => 'Printer',
                    'name_model' => 'HP LaserJet Pro',
                    'customer_name' => 'Global Industries',
                    'serial_license' => 'PR-004-GLOBAL',
                    'assigned_date' => 'Apr 5, 2024',
                    'warranty_expiry' => 'Apr 5, 2026',
                    'status' => 'active',
                ],
                [
                    'asset_id' => 'AST-005',
                    'category' => 'license',
                    'type_name' => 'License',
                    'name_model' => 'Microsoft Office 365',
                    'customer_name' => 'Innovate Labs',
                    'serial_license' => 'SL-005-INNOVATE',
                    'assigned_date' => 'Jan 15, 2024',
                    'warranty_expiry' => 'Jan 15, 2025',
                    'status' => 'active',
                ],
            ];

            foreach ($assets as $ast) {
                Inventory::create($ast);
            }
        }

        if (Renewal::count() === 0) {
            $yr = date('Y');
            $renewals = [
                [
                    'customer_name' => 'Acme Corp',
                    'type' => 'subscription',
                    'plan_asset' => 'Enterprise Plan',
                    'current_value' => '$2,450/month',
                    'renewal_date' => "Jun 15, {$yr}",
                    'days_left' => '3 days',
                    'priority' => 'urgent',
                ],
                [
                    'customer_name' => 'Tech Solutions',
                    'type' => 'subscription',
                    'plan_asset' => 'Professional Plan',
                    'current_value' => '$890/month',
                    'renewal_date' => "Jun 20, {$yr}",
                    'days_left' => '8 days',
                    'priority' => 'soon',
                ],
                [
                    'customer_name' => 'Global Industries',
                    'type' => 'warranty',
                    'plan_asset' => 'Desktop Computer',
                    'current_value' => '$1,200',
                    'renewal_date' => "Jun 25, {$yr}",
                    'days_left' => '13 days',
                    'priority' => 'normal',
                ],
                [
                    'customer_name' => 'Innovate Labs',
                    'type' => 'license',
                    'plan_asset' => 'Software License',
                    'current_value' => '$450',
                    'renewal_date' => "Jun 30, {$yr}",
                    'days_left' => '18 days',
                    'priority' => 'normal',
                ],
            ];

            foreach ($renewals as $ren) {
                Renewal::create($ren);
            }
        }

        $this->call(UpdateUsdAmountSeeder::class);

        // Seed Contract Types
        if (\App\Models\ContractType::count() === 0) {
            $types = [
                ['name' => 'Master Service Agreement (MSA)', 'description' => 'Overarching contract outlining terms of relationship', 'status' => 'active'],
                ['name' => 'Statement of Work (SOW)', 'description' => 'Document detailing specific work and deliverables', 'status' => 'active'],
                ['name' => 'Software License Agreement', 'description' => 'Terms of using software assets', 'status' => 'active'],
                ['name' => 'Data Processing Agreement', 'description' => 'Privacy and security terms for processing user data', 'status' => 'active'],
                ['name' => 'Vendor Service Agreement', 'description' => 'Agreements made with third-party service providers', 'status' => 'active'],
                ['name' => 'Professional Services Contract', 'description' => 'Agreements for specialized consulting services', 'status' => 'active'],
                ['name' => 'Technology Partnership Agreement', 'description' => 'Strategic partner terms', 'status' => 'active'],
                ['name' => 'API License Agreement', 'description' => 'Terms for developer portal and integration endpoints', 'status' => 'active'],
                ['name' => 'Hosting Services Contract', 'description' => 'Server hosting and SLA commitments', 'status' => 'active'],
                ['name' => 'Security Services Agreement', 'description' => 'IT auditing and compliance reviews', 'status' => 'inactive'], // One inactive for test
            ];

            foreach ($types as $type) {
                \App\Models\ContractType::create($type);
            }
        }

        // Seed sample Contracts
        if (\App\Models\Contract::count() === 0) {
            $msaType = \App\Models\ContractType::where('name', 'Master Service Agreement (MSA)')->first();
            $sowType = \App\Models\ContractType::where('name', 'Statement of Work (SOW)')->first();
            $acmeCust = \App\Models\Customer::where('name', 'Acme Corp')->first();
            $techCust = \App\Models\Customer::where('name', 'Tech Solutions')->first();

            if ($msaType && $acmeCust) {
                \App\Models\Contract::create([
                    'subject' => 'Enterprise SLA Core Agreement',
                    'value' => 50000.00,
                    'start_date' => date('Y-m-d', strtotime('-3 months')),
                    'end_date' => date('Y-m-d', strtotime('+9 months')),
                    'status' => 'active',
                    'contract_type_id' => $msaType->id,
                    'customer_id' => $acmeCust->id,
                    'customer_name' => $acmeCust->name,
                ]);
            }

            if ($sowType && $techCust) {
                \App\Models\Contract::create([
                    'subject' => 'Phase 2 Cloud Telemetry Rollout',
                    'value' => 12500.00,
                    'start_date' => date('Y-m-d', strtotime('-1 month')),
                    'end_date' => date('Y-m-d', strtotime('+2 months')),
                    'status' => 'active',
                    'contract_type_id' => $sowType->id,
                    'customer_id' => $techCust->id,
                    'customer_name' => $techCust->name,
                ]);
            }
        }
    }
}
