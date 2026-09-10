<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Inventory;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Renewal;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    /**
     * Compute runtime analytical models strictly sourced from authentic relational records populated across database ledgers.
     */
    public function index(Request $request): Response
    {
        \Illuminate\Support\Facades\Gate::authorize('reports.view');

        // Fetch all datasets from authentic relational database tables
        $customers = Customer::with('subCustomers:id,customer_id,name')->orderBy('joined_date')->get();
        $invoices = Invoice::orderBy('invoice_date')->get();
        $payments = Payment::with('subCustomer:id,name')->get();
        $subscriptions = Subscription::with('subCustomer:id,name')->orderBy('created_at')->get();
        $expenses = Expense::all();
        $purchases = Purchase::where('status', '!=', 'cancelled')->get();
        $expenseCategoriesList = Expense::select('category')->distinct()->pluck('category')->filter()->values()->toArray();

        // 1. Core KPIs
        $totalCustomers = $customers->count();
        $successfulPayments = $payments->where('status', 'successful');
        $totalRevenue = $successfulPayments->sum(fn($p) => $p->usd_amount ?? $p->amount);
        
        $failedPayments = $payments->where('status', 'failed');
        $failedTotalCount = $failedPayments->count();
        $failedTotalSum = $failedPayments->sum('amount');

        $activeSubsCount = $subscriptions->where('status', 'active')->count();

        // Calculate New/Renewed unique customer counts from Customer table (new) and Payments (renewed)
        $newCustomersCount = $totalCustomers;

        $renewedCustomersCount = Payment::where('status', 'successful')
            ->where('payment_context', 'renewal')
            ->selectRaw('COALESCE(customer_id, customer_name) as unique_cust')
            ->distinct()
            ->get()
            ->count();

        $totalCustomerActivity = $newCustomersCount + $renewedCustomersCount;

        // Utility helper to normalize text dates to Year-Month (YYYY-MM) and pretty format (Month YYYY)
        $getMonthKeys = function ($dateStr) {
            if (!$dateStr) {
                return ['key' => 'Unknown', 'label' => 'Unknown'];
            }
            try {
                $time = strtotime($dateStr);
                if (!$time) {
                    return ['key' => 'Unknown', 'label' => 'Unknown'];
                }
                return [
                    'key' => date('Y-m', $time),
                    'label' => date('F Y', $time)
                ];
            } catch (\Exception $e) {
                return ['key' => 'Unknown', 'label' => 'Unknown'];
            }
        };

        // Group Customers based on signup date (new) and renewal payments (renewed)
        $groupedCustomers = [];
        foreach ($customers as $cust) {
            $dates = $getMonthKeys($cust->joined_date ?: $cust->created_at->format('Y-m-d'));
            $key = $dates['key'];
            if (!isset($groupedCustomers[$key])) {
                $groupedCustomers[$key] = [
                    'month' => $dates['label'],
                    'new_customers' => [],
                    'renewed_customers' => [],
                    'items' => []
                ];
            }

            $ident = $cust->id ?: $cust->name;
            if (!in_array($ident, $groupedCustomers[$key]['new_customers'])) {
                $groupedCustomers[$key]['new_customers'][] = $ident;
            }

            $groupedCustomers[$key]['items'][] = [
                'name' => $cust->name,
                'email' => $cust->email,
                'status' => $cust->status,
                'details' => 'Joined' . ($cust->status === 'trial' ? ' (Trial)' : ' (Active)'),
                'date' => $cust->joined_date,
                'payment_context' => 'new',
                'sub_customers' => $cust->subCustomers->pluck('name')->toArray(),
            ];
        }

        foreach ($payments->where('status', 'successful')->where('payment_context', 'renewal') as $pay) {
            $dates = $getMonthKeys($pay->payment_date ?: $pay->created_at->format('Y-m-d'));
            $key = $dates['key'];
            if (!isset($groupedCustomers[$key])) {
                $groupedCustomers[$key] = [
                    'month' => $dates['label'],
                    'new_customers' => [],
                    'renewed_customers' => [],
                    'items' => []
                ];
            }

            $ident = $pay->customer_id ?: $pay->customer_name;
            if (!in_array($ident, $groupedCustomers[$key]['renewed_customers'])) {
                $groupedCustomers[$key]['renewed_customers'][] = $ident;
            }

            $groupedCustomers[$key]['items'][] = [
                'name' => $pay->customer_name,
                'email' => $pay->transaction_id,
                'status' => $pay->status,
                'details' => 'Renewal - Paid: $' . number_format($pay->amount, 2),
                'date' => $pay->payment_date,
                'payment_context' => 'renewal',
                'sub_customer_name' => $pay->subCustomer?->name,
            ];
        }

        // Convert the lists to unique counts for index rendering
        foreach ($groupedCustomers as $k => $val) {
            $groupedCustomers[$k]['new_count'] = count($val['new_customers']);
            $groupedCustomers[$k]['renewed_count'] = count($val['renewed_customers']);
        }

        // Group Total Sales (All Invoices)
        $groupedSales = [];
        foreach ($invoices as $inv) {
            $dates = $getMonthKeys($inv->invoice_date ?: $inv->created_at->format('Y-m-d'));
            $key = $dates['key'];
            if (!isset($groupedSales[$key])) {
                $groupedSales[$key] = [
                    'month' => $dates['label'],
                    'total' => 0.0,
                    'count' => 0,
                    'items' => []
                ];
            }
            $groupedSales[$key]['total'] += (float)$inv->total;
            $groupedSales[$key]['count']++;
            $groupedSales[$key]['items'][] = [
                'name' => $inv->customer_name,
                'email' => $inv->invoice_number,
                'status' => $inv->status,
                'details' => 'Total: $' . number_format($inv->total, 2),
                'date' => $inv->invoice_date
            ];
        }

        // Group Received Payments (Successful Payments)
        $groupedReceived = [];
        foreach ($payments->where('status', 'successful') as $pay) {
            $dates = $getMonthKeys($pay->payment_date ?: $pay->created_at->format('Y-m-d'));
            $key = $dates['key'];
            if (!isset($groupedReceived[$key])) {
                $groupedReceived[$key] = [
                    'month' => $dates['label'],
                    'total' => 0.0,
                    'count' => 0,
                    'items' => []
                ];
            }
            $groupedReceived[$key]['total'] += (float)($pay->usd_amount ?? $pay->amount);
            $groupedReceived[$key]['count']++;
            $groupedReceived[$key]['items'][] = [
                'name' => $pay->customer_name,
                'email' => $pay->transaction_id,
                'status' => $pay->status,
                'details' => 'Paid: $' . number_format($pay->usd_amount ?? $pay->amount, 2) . ' via ' . $pay->payment_method,
                'date' => $pay->payment_date,
                'sub_customer_name' => $pay->subCustomer?->name,
            ];
        }

        // Group Pending Payments (Pending/Overdue Invoices)
        $groupedPending = [];
        foreach ($invoices->whereIn('status', ['pending', 'overdue']) as $inv) {
            $dates = $getMonthKeys($inv->invoice_date ?: $inv->created_at->format('Y-m-d'));
            $key = $dates['key'];
            if (!isset($groupedPending[$key])) {
                $groupedPending[$key] = [
                    'month' => $dates['label'],
                    'total' => 0.0,
                    'count' => 0,
                    'items' => []
                ];
            }
            $groupedPending[$key]['total'] += (float)$inv->total;
            $groupedPending[$key]['count']++;
            $groupedPending[$key]['items'][] = [
                'name' => $inv->customer_name,
                'email' => $inv->invoice_number,
                'status' => $inv->status,
                'details' => 'Due: $' . number_format($inv->total, 2),
                'date' => $inv->due_date
            ];
        }

        // Group Subscriptions
        $groupedSubs = [];
        foreach ($subscriptions as $sub) {
            $dates = $getMonthKeys($sub->created_at->format('Y-m-d'));
            $key = $dates['key'];
            if (!isset($groupedSubs[$key])) {
                $groupedSubs[$key] = [
                    'month' => $dates['label'],
                    'count' => 0,
                    'items' => []
                ];
            }
            $groupedSubs[$key]['count']++;
            $groupedSubs[$key]['items'][] = [
                'name' => $sub->customer_name,
                'email' => $sub->customer_email,
                'status' => $sub->status,
                'details' => $sub->plan_name . ' ($' . number_format($sub->amount, 2) . '/' . $sub->billing_cycle . ')',
                'date' => $sub->next_billing_date,
                'sub_customer_name' => $sub->subCustomer?->name,
            ];
        }

        // Group Renewals
        $renewals = Renewal::all();
        $groupedRenewals = [];
        foreach ($renewals as $ren) {
            $dates = $getMonthKeys($ren->renewal_date ?: $ren->created_at->format('Y-m-d'));
            $key = $dates['key'];
            if (!isset($groupedRenewals[$key])) {
                $groupedRenewals[$key] = [
                    'month' => $dates['label'],
                    'count' => 0,
                    'items' => []
                ];
            }
            $groupedRenewals[$key]['count']++;
            $groupedRenewals[$key]['items'][] = [
                'name' => $ren->customer_name,
                'email' => $ren->type . ' - ' . $ren->plan_asset,
                'status' => $ren->priority,
                'details' => 'Value: $' . number_format((float)$ren->current_value, 2) . ' (' . $ren->days_left . ' days left)',
                'date' => $ren->renewal_date,
            ];
        }

        // Sort Helper
        $sortGroups = function ($arr) {
            krsort($arr);
            return array_values($arr);
        };

        // Pre-aggregate monthly metrics for all years to allow instant client-side YoY/MoM calculations
        $comparisonData = [];
        $minYear = (int)date('Y') - 5;
        $maxYear = (int)date('Y');
        
        $allDates = collect()
            ->concat($customers->map(fn($c) => $c->joined_date ? date('Y', strtotime($c->joined_date)) : $c->created_at->format('Y')))
            ->concat($invoices->map(fn($i) => $i->invoice_date ? date('Y', strtotime($i->invoice_date)) : $i->created_at->format('Y')))
            ->concat($payments->map(fn($p) => $p->payment_date ? date('Y', strtotime($p->payment_date)) : $p->created_at->format('Y')))
            ->concat($renewals->map(fn($r) => $r->renewal_date ? date('Y', strtotime($r->renewal_date)) : $r->created_at->format('Y')))
            ->filter();
            
        if ($allDates->isNotEmpty()) {
            $minYear = (int)$allDates->min();
            $maxYear = (int)$allDates->max();
        }
        $minYear = min($minYear, (int)date('Y'));
        $maxYear = max($maxYear, (int)date('Y'));
        
        $availableYearsList = [];
        for ($y = $maxYear; $y >= $minYear; $y--) {
            $availableYearsList[] = (string)$y;
        }

        // Initialize grid (include one extra year before minYear for YoY reference)
        for ($y = $minYear - 1; $y <= $maxYear; $y++) {
            $comparisonData[$y] = [];
            for ($m = 1; $m <= 12; $m++) {
                $comparisonData[$y][$m] = [
                    'sales' => 0.0,
                    'revenue' => 0.0,
                    'customers' => 0,
                    'outstanding' => 0.0,
                    'expenses' => 0.0,
                    'purchases' => 0.0,
                    'expense_categories' => [],
                ];
            }
        }

        // Populate Sales
        foreach ($invoices as $inv) {
            $time = strtotime($inv->invoice_date ?: $inv->created_at->format('Y-m-d'));
            if ($time) {
                $y = (int)date('Y', $time);
                $m = (int)date('n', $time);
                if (isset($comparisonData[$y][$m])) {
                    $comparisonData[$y][$m]['sales'] += (float)$inv->total;
                    
                    // Populate Outstanding if pending or overdue
                    if (in_array($inv->status, ['pending', 'overdue'])) {
                        $comparisonData[$y][$m]['outstanding'] += (float)$inv->total;
                    }
                }
            }
        }

        // Populate Revenue
        foreach ($payments->where('status', 'successful') as $pay) {
            $time = strtotime($pay->payment_date ?: $pay->created_at->format('Y-m-d'));
            if ($time) {
                $y = (int)date('Y', $time);
                $m = (int)date('n', $time);
                if (isset($comparisonData[$y][$m])) {
                    $comparisonData[$y][$m]['revenue'] += (float)($pay->usd_amount ?? $pay->amount);
                }
            }
        }

        // Populate Customers
        foreach ($customers as $cust) {
            $time = strtotime($cust->joined_date ?: $cust->created_at->format('Y-m-d'));
            if ($time) {
                $y = (int)date('Y', $time);
                $m = (int)date('n', $time);
                if (isset($comparisonData[$y][$m])) {
                    $comparisonData[$y][$m]['customers']++;
                }
            }
        }

        // Populate Expenses
        foreach ($expenses as $exp) {
            $time = strtotime($exp->expense_date ?: $exp->created_at->format('Y-m-d'));
            if ($time) {
                $y = (int)date('Y', $time);
                $m = (int)date('n', $time);
                if (isset($comparisonData[$y][$m])) {
                    $comparisonData[$y][$m]['expenses'] += (float)$exp->amount;
                    $cat = $exp->category ?: 'Other';
                    if (!isset($comparisonData[$y][$m]['expense_categories'][$cat])) {
                        $comparisonData[$y][$m]['expense_categories'][$cat] = 0.0;
                    }
                    $comparisonData[$y][$m]['expense_categories'][$cat] += (float)$exp->amount;
                }
            }
        }

        // Populate Purchases
        foreach ($purchases as $pur) {
            $time = strtotime($pur->purchase_date ?: $pur->created_at->format('Y-m-d'));
            if ($time) {
                $y = (int)date('Y', $time);
                $m = (int)date('n', $time);
                if (isset($comparisonData[$y][$m])) {
                    $comparisonData[$y][$m]['purchases'] += (float)$pur->total_amount;
                }
            }
        }

        // Fetch top 5 paid customers
        $topPaidCustomers = Payment::where('status', 'successful')
            ->select('customer_name')
            ->selectRaw('SUM(usd_amount) as total_spent')
            ->groupBy('customer_name')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get()
            ->map(function ($c) {
                return [
                    'name' => $c->customer_name,
                    'total_spent' => '$' . number_format($c->total_spent, 2),
                    'raw_total_spent' => (float)$c->total_spent,
                    'payments_count' => Payment::where('customer_name', $c->customer_name)
                        ->where('status', 'successful')
                        ->count(),
                ];
            });

        // Generate list containing previous 10 years and next 10 years of the current year
        $currentYear = (int)date('Y');
        $availableYears = [];
        for ($y = $currentYear - 10; $y <= $currentYear + 10; $y++) {
            $availableYears[] = (string)$y;
        }
        rsort($availableYears);

        return Inertia::render('Reports/Index', [
            'metrics' => [
                'total_revenue' => '$' . number_format($totalRevenue, 2),
                'customer_growth' => $totalCustomers . ' Accounts',
                'failed_payments_count' => $failedTotalCount,
                'failed_payments_sum' => '$' . number_format($failedTotalSum, 2),
                'active_subscriptions' => $activeSubsCount,
                'new_customers' => $newCustomersCount,
                'renewed_customers' => $renewedCustomersCount,
                'total_customer_activity' => $totalCustomerActivity,
            ],
            'reportsData' => [
                'customers' => $sortGroups($groupedCustomers),
                'totalSales' => $sortGroups($groupedSales),
                'receivedPayments' => $sortGroups($groupedReceived),
                'pendingPayments' => $sortGroups($groupedPending),
                'subscriptions' => $sortGroups($groupedSubs),
                'renewals' => $sortGroups($groupedRenewals),
            ],
            'topPaidCustomers' => $topPaidCustomers,
            'comparisonData' => $comparisonData,
            'dynamicYears' => $availableYearsList,
            'availableYears' => $availableYears,
            'expenseCategories' => $expenseCategoriesList,
        ]);
    }

    /**
     * Display a separate page listing items for a specific month and category.
     */
    public function detail(Request $request): Response
    {
        \Illuminate\Support\Facades\Gate::authorize('reports.view');

        $type = $request->input('type');
        
        $customers = Customer::with('subCustomers:id,customer_id,name')->orderBy('joined_date')->get();
        $invoices = Invoice::orderBy('invoice_date')->get();
        $payments = Payment::with('subCustomer:id,name')->get();
        $subscriptions = Subscription::with('subCustomer:id,name')->orderBy('created_at')->get();

        // Helper to extract year and full month name
        $getDateParts = function ($dateStr) {
            if (!$dateStr) return ['year' => null, 'month_name' => null, 'month_year_label' => 'Unknown'];
            try {
                $time = strtotime($dateStr);
                if (!$time) return ['year' => null, 'month_name' => null, 'month_year_label' => 'Unknown'];
                return [
                    'year' => date('Y', $time),
                    'month_name' => date('F', $time),
                    'month_year_label' => date('F Y', $time)
                ];
            } catch (\Exception $e) {
                return ['year' => null, 'month_name' => null, 'month_year_label' => 'Unknown'];
            }
        };

        // Generate list containing previous 10 years and next 10 years of the current year
        $currentYear = (int)date('Y');
        $availableYears = [];
        for ($y = $currentYear - 10; $y <= $currentYear + 10; $y++) {
            $availableYears[] = (string)$y;
        }
        rsort($availableYears);

        // Parse initial input parameter which was "Month Year" format, e.g. "May 2026"
        $inputMonthParam = $request->input('month');
        $selectedMonth = $request->input('filter_month');
        $selectedYear = $request->input('filter_year');

        if (!$selectedMonth || !$selectedYear) {
            if ($inputMonthParam && strpos($inputMonthParam, ' ') !== false) {
                list($mName, $yVal) = explode(' ', $inputMonthParam, 2);
                $selectedMonth = $selectedMonth ?: $mName;
                $selectedYear = $selectedYear ?: $yVal;
            } else {
                $selectedMonth = $selectedMonth ?: date('F');
                $selectedYear = $selectedYear ?: (string)$currentYear;
            }
        }

        $items = [];

        if ($type === 'customers') {
            // Load new customer signups from customers table
            foreach ($customers as $cust) {
                $parts = $getDateParts($cust->joined_date ?: $cust->created_at->format('Y-m-d'));
                if ($parts['month_name'] === $selectedMonth && $parts['year'] === $selectedYear) {
                    $custSubs = Subscription::with('subCustomer:id,name')
                        ->where('customer_name', $cust->name)
                        ->get()
                        ->map(fn($s) => [
                            'plan_name' => $s->plan_name,
                            'amount' => (float)$s->amount,
                            'status' => $s->status,
                            'billing_cycle' => $s->billing_cycle,
                            'sub_customer_name' => $s->subCustomer?->name,
                        ]);

                    $custInvoices = Invoice::where('customer_name', $cust->name)
                        ->get()
                        ->map(fn($i) => [
                            'invoice_number' => $i->invoice_number,
                            'total' => (float)$i->total,
                            'status' => $i->status,
                            'due_date' => $i->due_date,
                        ]);

                    $custPayments = Payment::with('subCustomer:id,name')
                        ->where('customer_name', $cust->name)
                        ->get()
                        ->map(fn($p) => [
                            'transaction_id' => $p->transaction_id,
                            'amount' => (float)$p->amount,
                            'status' => $p->status,
                            'payment_date' => $p->payment_date,
                            'sub_customer_name' => $p->subCustomer?->name,
                        ]);

                    $custRenewals = Renewal::where('customer_name', $cust->name)->get()
                        ->map(fn($r) => [
                            'type' => $r->type,
                            'plan_asset' => $r->plan_asset,
                            'current_value' => (float)$r->current_value,
                            'renewal_date' => $r->renewal_date,
                            'days_left' => $r->days_left,
                            'priority' => $r->priority,
                        ]);

                    $hasPendingSubscription = $custSubs->isEmpty() || $custSubs->contains(fn($s) => in_array(strtolower($s['status']), ['pending', 'trial', 'paused', 'cancelled']));
                    $hasPendingPayment = $custInvoices->contains(fn($i) => in_array(strtolower($i['status']), ['pending', 'overdue'])) || $custPayments->contains(fn($p) => strtolower($p['status']) === 'failed');
                    $hasPendingRenewal = $custRenewals->contains(fn($r) => in_array(strtolower($r['priority']), ['high', 'medium', 'expired']));

                    $items[] = [
                        'name' => $cust->name,
                        'email' => $cust->email,
                        'status' => $cust->status,
                        'details' => 'Joined' . ($cust->status === 'trial' ? ' (Trial)' : ' (Active)'),
                        'date' => $cust->joined_date,
                        'payment_context' => 'new',
                        'transaction_id' => $cust->email,
                        'amount' => 0.0,
                        'payment_status' => 'successful',
                        'subscriptions' => $custSubs,
                        'invoices' => $custInvoices,
                        'payments' => $custPayments,
                        'renewals' => $custRenewals,
                        'has_pending_subscription' => $hasPendingSubscription,
                        'has_pending_payment' => $hasPendingPayment,
                        'has_pending_renewal' => $hasPendingRenewal,
                        'sub_customers' => $cust->subCustomers->pluck('name')->toArray(),
                    ];
                }
            }

            // Load renewal payments from payments table
            $monthPayments = Payment::with('subCustomer:id,name')
                ->where('status', 'successful')
                ->where('payment_context', 'renewal')
                ->get();
            foreach ($monthPayments as $pay) {
                $parts = $getDateParts($pay->payment_date ?: $pay->created_at->format('Y-m-d'));
                if ($parts['month_name'] === $selectedMonth && $parts['year'] === $selectedYear) {
                    $customerObj = Customer::where('name', $pay->customer_name)->first();
                    $email = $customerObj ? $customerObj->email : '';
                    $status = $customerObj ? $customerObj->status : 'active';

                    $custSubs = Subscription::with('subCustomer:id,name')
                        ->where('customer_name', $pay->customer_name)
                        ->get()
                        ->map(fn($s) => [
                            'plan_name' => $s->plan_name,
                            'amount' => (float)$s->amount,
                            'status' => $s->status,
                            'billing_cycle' => $s->billing_cycle,
                            'sub_customer_name' => $s->subCustomer?->name,
                        ]);

                    $custInvoices = Invoice::where('customer_name', $pay->customer_name)
                        ->get()
                        ->map(fn($i) => [
                            'invoice_number' => $i->invoice_number,
                            'total' => (float)$i->total,
                            'status' => $i->status,
                            'due_date' => $i->due_date,
                        ]);

                    $custPayments = Payment::with('subCustomer:id,name')
                        ->where('customer_name', $pay->customer_name)
                        ->get()
                        ->map(fn($p) => [
                            'transaction_id' => $p->transaction_id,
                            'amount' => (float)$p->amount,
                            'status' => $p->status,
                            'payment_date' => $p->payment_date,
                            'sub_customer_name' => $p->subCustomer?->name,
                        ]);

                    $custRenewals = Renewal::where('customer_name', $pay->customer_name)->get()
                        ->map(fn($r) => [
                            'type' => $r->type,
                            'plan_asset' => $r->plan_asset,
                            'current_value' => (float)$r->current_value,
                            'renewal_date' => $r->renewal_date,
                            'days_left' => $r->days_left,
                            'priority' => $r->priority,
                        ]);

                    $hasPendingSubscription = $custSubs->isEmpty() || $custSubs->contains(fn($s) => in_array(strtolower($s['status']), ['pending', 'trial', 'paused', 'cancelled']));
                    $hasPendingPayment = $custInvoices->contains(fn($i) => in_array(strtolower($i['status']), ['pending', 'overdue'])) || $custPayments->contains(fn($p) => strtolower($p['status']) === 'failed');
                    $hasPendingRenewal = $custRenewals->contains(fn($r) => in_array(strtolower($r['priority']), ['high', 'medium', 'expired']));

                    $items[] = [
                        'name' => $pay->customer_name,
                        'email' => $email,
                        'status' => $status,
                        'details' => 'Renewal - Paid: $' . number_format($pay->amount, 2),
                        'date' => $pay->payment_date,
                        'payment_context' => $pay->payment_context,
                        'transaction_id' => $pay->transaction_id,
                        'amount' => (float)$pay->amount,
                        'payment_status' => $pay->status,
                        'subscriptions' => $custSubs,
                        'invoices' => $custInvoices,
                        'payments' => $custPayments,
                        'renewals' => $custRenewals,
                        'has_pending_subscription' => $hasPendingSubscription,
                        'has_pending_payment' => $hasPendingPayment,
                        'has_pending_renewal' => $hasPendingRenewal,
                        'sub_customer_name' => $pay->subCustomer?->name,
                    ];
                }
            }
        } elseif ($type === 'totalSales') {
            foreach ($invoices as $inv) {
                $parts = $getDateParts($inv->invoice_date ?: $inv->created_at->format('Y-m-d'));
                if ($parts['month_name'] === $selectedMonth && $parts['year'] === $selectedYear) {
                    $items[] = [
                        'name' => $inv->customer_name,
                        'email' => $inv->invoice_number,
                        'status' => $inv->status,
                        'details' => 'Total: $' . number_format($inv->total, 2),
                        'date' => $inv->invoice_date
                    ];
                }
            }
        } elseif ($type === 'receivedPayments') {
            foreach ($payments->where('status', 'successful') as $pay) {
                $parts = $getDateParts($pay->payment_date ?: $pay->created_at->format('Y-m-d'));
                if ($parts['month_name'] === $selectedMonth && $parts['year'] === $selectedYear) {
                    $items[] = [
                        'name' => $pay->customer_name,
                        'email' => $pay->transaction_id,
                        'status' => $pay->status,
                        'details' => 'Paid: $' . number_format($pay->usd_amount ?? $pay->amount, 2) . ' via ' . $pay->payment_method,
                        'date' => $pay->payment_date,
                        'sub_customer_name' => $pay->subCustomer?->name,
                    ];
                }
            }
        } elseif ($type === 'pendingPayments') {
            foreach ($invoices->whereIn('status', ['pending', 'overdue']) as $inv) {
                $parts = $getDateParts($inv->invoice_date ?: $inv->created_at->format('Y-m-d'));
                if ($parts['month_name'] === $selectedMonth && $parts['year'] === $selectedYear) {
                    $items[] = [
                        'name' => $inv->customer_name,
                        'email' => $inv->invoice_number,
                        'status' => $inv->status,
                        'details' => 'Due: $' . number_format($inv->total, 2),
                        'date' => $inv->due_date
                    ];
                }
            }
        } elseif ($type === 'subscriptions') {
            foreach ($subscriptions as $sub) {
                $parts = $getDateParts($sub->created_at->format('Y-m-d'));
                if ($parts['month_name'] === $selectedMonth && $parts['year'] === $selectedYear) {
                    $items[] = [
                        'name' => $sub->customer_name,
                        'email' => $sub->customer_email,
                        'status' => $sub->status,
                        'details' => $sub->plan_name . ' ($' . number_format($sub->amount, 2) . '/' . $sub->billing_cycle . ')',
                        'date' => $sub->next_billing_date,
                        'sub_customer_name' => $sub->subCustomer?->name,
                    ];
                }
            }
        } elseif ($type === 'renewals') {
            $renewals = Renewal::all();
            foreach ($renewals as $ren) {
                $parts = $getDateParts($ren->renewal_date ?: $ren->created_at->format('Y-m-d'));
                if ($parts['month_name'] === $selectedMonth && $parts['year'] === $selectedYear) {
                    $items[] = [
                        'name' => $ren->customer_name,
                        'email' => $ren->type . ' - ' . $ren->plan_asset,
                        'status' => $ren->priority,
                        'details' => 'Value: $' . number_format((float)$ren->current_value, 2) . ' (' . $ren->days_left . ' days left)',
                        'date' => $ren->renewal_date,
                    ];
                }
            }
        }

        // Clean label formatting for title
        $titleMapping = [
            'customers' => 'Customer Activity',
            'totalSales' => 'Total Sales',
            'receivedPayments' => 'Received Payments',
            'pendingPayments' => 'Pending Payments',
            'subscriptions' => 'Subscriptions',
            'renewals' => 'Renewals'
        ];
        $typeName = $titleMapping[$type] ?? ucfirst($type);

        $monthsList = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        return Inertia::render('Reports/Detail', [
            'type' => $type,
            'typeName' => $typeName,
            'month' => $selectedMonth . ' ' . $selectedYear,
            'filterMonth' => $selectedMonth,
            'filterYear' => $selectedYear,
            'items' => $items,
            'availableMonths' => $monthsList,
            'availableYears' => $availableYears
        ]);
    }
}
