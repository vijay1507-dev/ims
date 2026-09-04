<?php

namespace App\Http\Controllers;

use App\Models\Renewal;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\BillingCycle;
use App\Models\Invoice;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class RenewalController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Renewal::class, 'renewal');
    }

    /**
     * Display a live real-time dashboard of upcoming customer contract deadlines and warranty schedules.
     */
    public function index(Request $request): Response
    {
        $query = Renewal::query();

        // Reactive Type filter
        if ($request->filled('type') && $request->type !== 'All Types') {
            $typeMap = [
                'subscription' => 'subscription',
                'payment' => 'payment',
                'warranty' => 'warranty',
                'license' => 'license',
                'hardware' => 'warranty',
                'Subscriptions' => 'subscription',
                'Payments' => 'payment',
                'Warranties' => 'warranty',
                'Licenses' => 'license',
            ];
            if (isset($typeMap[$request->type])) {
                $query->where('type', $typeMap[$request->type]);
            }
        }

        // Reactive Priority filter
        if ($request->filled('priority') && !in_array($request->priority, ['All Status', 'All Priorities'])) {
            $query->where('priority', strtolower($request->priority));
        }

        // Active textual search query logic
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('customer_name', 'like', "%{$searchTerm}%")
                  ->orWhere('plan_asset', 'like', "%{$searchTerm}%")
                  ->orWhere('current_value', 'like', "%{$searchTerm}%");
            });
        }

        $currentYear = date('Y');

        $formatDateStr = function ($dateStr) {
            if (empty($dateStr)) return '';
            $ts = strtotime($dateStr);
            if ($ts === false) {
                // Try parsing DD-MM-YYYY format
                if (preg_match('/^(\d{1,2})[-|\/](\d{1,2})[-|\/](\d{4})$/', trim($dateStr), $matches)) {
                    $ts = mktime(0, 0, 0, (int)$matches[2], (int)$matches[1], (int)$matches[3]);
                }
            }
            return $ts !== false ? date('M d, Y', $ts) : $dateStr;
        };

        $dbRenewals = $query->latest()->get()->map(function ($ren) use ($currentYear, $formatDateStr) {
            $dateStr = $ren->renewal_date;
            if ($dateStr && str_ends_with($dateStr, '2024')) {
                $dateStr = str_replace('2024', $currentYear, $dateStr);
            }
            $dateStr = $formatDateStr($dateStr);

            $daysLeftStr = $ren->days_left;
            $daysLeftVal = 30;
            $ts = strtotime($dateStr);
            if ($ts !== false) {
                $diff = round(($ts - time()) / 86400);
                $daysLeftVal = (int)$diff;
                $daysLeftStr = ($daysLeftVal < 0 ? 0 : $daysLeftVal) . ' days';
            }

            $priority = $ren->priority;
            if ($daysLeftVal <= 0) {
                $priority = 'expired';
            }

            return [
                'id' => $ren->id,
                'customer_name' => $ren->customer_name,
                'type' => $ren->type,
                'plan_asset' => $ren->plan_asset,
                'current_value' => $ren->current_value ?? '$0',
                'renewal_date' => $dateStr ?? $ren->created_at->addDays(30)->format('M d, Y'),
                'days_left' => $daysLeftStr,
                'priority' => $priority,
                'renewal_reminders' => $ren->renewal_reminders ?? '7_days',
            ];
        });

        // Load active subscriptions and convert to renewal entities
        $subQuery = \App\Models\Subscription::query();
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $subQuery->where(function ($q) use ($searchTerm) {
                $q->where('customer_name', 'like', "%{$searchTerm}%")
                  ->orWhere('plan_name', 'like', "%{$searchTerm}%");
            });
        }

        $subscriptions = $subQuery->get()->map(function ($sub) use ($currentYear, $formatDateStr) {
            $dateStr = $sub->next_billing_date;
            if ($dateStr && str_ends_with($dateStr, '2024')) {
                $dateStr = str_replace('2024', $currentYear, $dateStr);
            }
            if ($dateStr && str_ends_with($dateStr, '2025')) {
                $dateStr = str_replace('2025', $currentYear, $dateStr);
            }
            $dateStr = $formatDateStr($dateStr);

            $daysLeft = 30;
            $ts = strtotime($dateStr);
            if ($ts !== false) {
                $diff = round(($ts - time()) / 86400);
                $daysLeft = (int)$diff;
            }

            $priority = 'normal';
            if ($daysLeft <= 0) {
                $priority = 'expired';
            } elseif ($daysLeft <= 7) {
                $priority = 'urgent';
            } elseif ($daysLeft <= 30) {
                $priority = 'soon';
            }

            // Find renewal reminders sequence if scheduled
            $reminders = '7_days';
            $matchingRenewal = Renewal::where('customer_name', $sub->customer_name)->first();
            if ($matchingRenewal && $matchingRenewal->renewal_reminders) {
                $reminders = $matchingRenewal->renewal_reminders;
            }

            return [
                'id' => 'sub_' . $sub->id,
                'customer_name' => $sub->customer_name,
                'type' => 'subscription',
                'plan_asset' => $sub->plan_name,
                'current_value' => '$' . number_format($sub->amount, 2),
                'renewal_date' => $dateStr ?? now()->addDays(30)->format('M d, Y'),
                'days_left' => ($daysLeft < 0 ? 0 : $daysLeft) . ' days',
                'priority' => $priority,
                'renewal_reminders' => $reminders,
            ];
        });

        // Load active payments and convert to renewal entities
        $payQuery = \App\Models\Payment::query()->whereIn('status', ['successful', 'pending']);
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $payQuery->where(function ($q) use ($searchTerm) {
                $q->where('customer_name', 'like', "%{$searchTerm}%")
                  ->orWhere('invoice_ref', 'like', "%{$searchTerm}%");
            });
        }

        $paymentsAsRenewals = $payQuery->get()
            ->groupBy('customer_name')
            ->map(function ($group) {
                return $group->sortByDesc(function ($pay) {
                    $dateStr = $pay->end_date ?? $pay->payment_date ?? '';
                    $ts = strtotime($dateStr);
                    return $ts !== false ? $ts : 0;
                })->first();
            })
            ->values()
            ->map(function ($pay) use ($currentYear, $formatDateStr) {
                $dateStr = $pay->end_date;
                if ($dateStr && str_ends_with($dateStr, '2024')) {
                    $dateStr = str_replace('2024', $currentYear, $dateStr);
                }
                if ($dateStr && str_ends_with($dateStr, '2025')) {
                    $dateStr = str_replace('2025', $currentYear, $dateStr);
                }
                $dateStr = $formatDateStr($dateStr);

                $daysLeft = 30;
                $ts = strtotime($dateStr);
                if ($ts !== false) {
                    $diff = round(($ts - time()) / 86400);
                    $daysLeft = (int)$diff;
                }

                $priority = 'normal';
                if ($daysLeft <= 0) {
                    $priority = 'expired';
                } elseif ($daysLeft <= 7) {
                    $priority = 'urgent';
                } elseif ($daysLeft <= 30) {
                    $priority = 'soon';
                }

                // Find renewal reminders sequence if scheduled
                $reminders = '7_days';
                $matchingRenewal = Renewal::where('customer_name', $pay->customer_name)->first();
                if ($matchingRenewal && $matchingRenewal->renewal_reminders) {
                    $reminders = $matchingRenewal->renewal_reminders;
                }

                return [
                    'id' => 'pay_' . $pay->id,
                    'customer_name' => $pay->customer_name,
                    'type' => 'payment',
                    'plan_asset' => 'Payment Cycle (' . ($pay->billing_cycle ? ucfirst($pay->billing_cycle) : 'N/A') . ')',
                    'current_value' => '$' . number_format($pay->amount, 2),
                    'renewal_date' => $dateStr ?? now()->addDays(30)->format('M d, Y'),
                    'days_left' => ($daysLeft < 0 ? 0 : $daysLeft) . ' days',
                    'priority' => $priority,
                    'renewal_reminders' => $reminders,
                ];
            });

        // Filter subscriptions and payments in memory based on request filters
        if ($request->filled('type') && $request->type !== 'All Types') {
            $typeMapMemory = [
                'subscription' => 'subscription',
                'payment' => 'payment',
                'warranty' => 'warranty',
                'license' => 'license',
                'hardware' => 'warranty',
                'Subscriptions' => 'subscription',
                'Payments' => 'payment',
                'Warranties' => 'warranty',
                'Licenses' => 'license',
            ];
            $selectedType = $typeMapMemory[$request->type] ?? null;
            if ($selectedType) {
                $subscriptions = $subscriptions->filter(function ($item) use ($selectedType) {
                    return $item['type'] === $selectedType;
                });
                $paymentsAsRenewals = $paymentsAsRenewals->filter(function ($item) use ($selectedType) {
                    return $item['type'] === $selectedType;
                });
            }
        }

        if ($request->filled('priority') && !in_array($request->priority, ['All Status', 'All Priorities'])) {
            $selectedPriority = strtolower($request->priority);
            $subscriptions = $subscriptions->filter(function ($item) use ($selectedPriority) {
                return $item['priority'] === $selectedPriority;
            });
            $paymentsAsRenewals = $paymentsAsRenewals->filter(function ($item) use ($selectedPriority) {
                return $item['priority'] === $selectedPriority;
            });
        }

        // Merge DB renewals, active Subscriptions, and active Payments
        $renewals = $dbRenewals->concat($subscriptions)->concat($paymentsAsRenewals)->values();

        // Keep only upcoming (within 30 days) and expired renewals
        $renewals = $renewals->filter(function ($item) {
            return in_array($item['priority'], ['expired', 'urgent', 'soon']);
        })->values();

        // Month and Year Filters
        $monthsList = [
            'All Months', 'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        $currentYearInt = (int)date('Y');
        $availableYears = ['All Years'];
        for ($y = $currentYearInt - 10; $y <= $currentYearInt + 10; $y++) {
            $availableYears[] = (string)$y;
        }

        $selectedMonth = $request->input('filter_month', 'All Months');
        $selectedYear = $request->input('filter_year', 'All Years');

        if ($selectedMonth !== 'All Months' || $selectedYear !== 'All Years') {
            $renewals = $renewals->filter(function ($item) use ($selectedMonth, $selectedYear) {
                if (empty($item['renewal_date'])) {
                    return false;
                }
                $ts = strtotime($item['renewal_date']);
                if ($ts === false) {
                    return false;
                }
                $itemMonth = date('F', $ts);
                $itemYear = date('Y', $ts);

                if ($selectedMonth !== 'All Months' && $itemMonth !== $selectedMonth) {
                    return false;
                }
                if ($selectedYear !== 'All Years' && $itemYear !== $selectedYear) {
                    return false;
                }
                return true;
            })->values();
        }

        // Compute active time-based KPI metrics based on true calendar countdown intervals
        $upcomingCount = $renewals->filter(function ($r) {
            if ($r['priority'] === 'renewed') return false;
            $ts = strtotime($r['renewal_date']);
            return $ts !== false && $ts >= time() - 86400 && $ts <= strtotime('+30 days');
        })->count();

        $urgentCount = $renewals->filter(function ($r) {
            if ($r['priority'] === 'renewed') return false;
            $ts = strtotime($r['renewal_date']);
            return $ts !== false && $ts >= time() - 86400 && $ts <= strtotime('+7 days');
        })->count();

        $expiredCount = $renewals->filter(function ($r) {
            return $r['priority'] === 'expired';
        })->count();

        // Compute active metrics representation sums
        $totalEvaluated = $renewals->count();
        $renewedCount = $renewals->filter(function ($r) { return $r['priority'] === 'renewed'; })->count();
        $ratePercentage = $totalEvaluated > 0 ? round(($renewedCount / $totalEvaluated) * 100) : 0;

        if ($ratePercentage > 0) {
            $trendText = $ratePercentage . '% increase';
            $trendClass = 'text-green-600';
            $trendIcon = 'fas fa-arrow-up';
        } else {
            $trendText = 'No change';
            $trendClass = 'text-gray-500';
            $trendIcon = 'fas fa-minus';
        }

        // Load billing cycles for the renewal modal dropdown
        $billingCycles = BillingCycle::where('status', 'active')
            ->orderBy('duration_months')
            ->get(['id', 'name', 'code', 'duration_months'])
            ->map(fn($bc) => [
                'code'            => $bc->code,
                'name'            => $bc->name,
                'duration_months' => (int) $bc->duration_months,
            ]);

        // Fallback if no billing cycles seeded yet
        if ($billingCycles->isEmpty()) {
            $billingCycles = collect([
                ['code' => 'monthly',     'name' => 'Monthly',    'duration_months' => 1],
                ['code' => 'quarterly',   'name' => 'Quarterly',  'duration_months' => 3],
                ['code' => 'half_yearly', 'name' => 'Half Yearly','duration_months' => 6],
                ['code' => 'annual',      'name' => 'Annual',     'duration_months' => 12],
            ]);
        }

        return Inertia::render('Renewals/Index', [
            'metrics' => [
                'upcoming_renewals' => number_format($upcomingCount),
                'urgent_renewals'   => number_format($urgentCount),
                'expired_renewals'  => number_format($expiredCount),
                'auto_renewal_rate' => $ratePercentage . '%',
                'auto_renewal_trend' => [
                    'text'  => $trendText,
                    'class' => $trendClass,
                    'icon'  => $trendIcon,
                ],
            ],
            'renewals'        => $renewals,
            'availableMonths' => $monthsList,
            'availableYears'  => $availableYears,
            'billingCycles'   => $billingCycles,
            'filters'         => [
                'type'         => $request->type ?? 'All Types',
                'priority'     => $request->priority ?? 'All Status',
                'search'       => $request->search ?? '',
                'filter_month' => $selectedMonth,
                'filter_year'  => $selectedYear,
                'page'         => $request->page ?? 1,
            ],
        ]);
    }

    /**
     * Show view page supporting registration of new explicit recurring client lifecycles.
     */
    public function create(): Response
    {
        return Inertia::render('Renewals/Create');
    }

    /**
     * Commit custom provisioned customer billing deadlines and tracking alerts.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:subscription,warranty,license',
            'plan_asset' => 'nullable|string|max:255',
            'current_value' => 'nullable|string|max:255',
            'renewal_date' => 'nullable|string|max:255',
            'days_left' => 'nullable|string|max:255',
            'priority' => 'nullable|string|in:urgent,soon,normal,renewed',
            'renewal_reminders' => 'nullable|string|max:50',
        ]);

        if (empty($validated['customer_name'])) {
            $validated['customer_name'] = 'General Customer';
        }
        if (empty($validated['type'])) {
            $validated['type'] = 'subscription';
        }
        if (empty($validated['plan_asset'])) {
            $validated['plan_asset'] = 'General Subscription';
        }
        if (empty($validated['priority'])) {
            $validated['priority'] = 'normal';
        }
        if (empty($validated['renewal_date'])) {
            $validated['renewal_date'] = now()->addDays(30)->format('M d, Y');
        }
        if (empty($validated['days_left'])) {
            $validated['days_left'] = '30 days';
        }

        Renewal::create($validated);

        return redirect()->route('renewals.index');
    }

    /**
     * Render parameters patch layout mapping to pre-stored recurring tracking options.
     */
    public function edit(Request $request, Renewal $renewal): Response
    {
        return Inertia::render('Renewals/Edit', [
            'renewal' => [
                'id' => $renewal->id,
                'customer_name' => $renewal->customer_name,
                'type' => $renewal->type,
                'plan_asset' => $renewal->plan_asset,
                'current_value' => $renewal->current_value,
                'renewal_date' => $renewal->renewal_date,
                'days_left' => $renewal->days_left,
                'priority' => $renewal->priority,
                'renewal_reminders' => $renewal->renewal_reminders,
            ],
            'returnPage' => (int) $request->input('page', 1),
        ]);
    }

    /**
     * Update target persistence parameters inside active expiration matrices.
     */
    public function update(Request $request, Renewal $renewal): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:subscription,warranty,license',
            'plan_asset' => 'nullable|string|max:255',
            'current_value' => 'nullable|string|max:255',
            'renewal_date' => 'nullable|string|max:255',
            'days_left' => 'nullable|string|max:255',
            'priority' => 'nullable|string|in:urgent,soon,normal,renewed',
            'renewal_reminders' => 'nullable|string|max:50',
        ]);

        if (empty($validated['customer_name'])) {
            $validated['customer_name'] = 'General Customer';
        }
        if (empty($validated['type'])) {
            $validated['type'] = 'subscription';
        }
        if (empty($validated['plan_asset'])) {
            $validated['plan_asset'] = 'General Subscription';
        }
        if (empty($validated['priority'])) {
            $validated['priority'] = 'normal';
        }

        $renewal->update($validated);

        $page = $request->input('page', 1);
        return redirect()->route('renewals.index', ['page' => $page])->with('success', 'Renewal updated successfully.');
    }

    /**
     * Process a renewal action — creates a new payment record without touching the original.
     * Handles sub_* (subscription), pay_* (payment), and plain (db renewal) IDs.
     */
    public function renew(Request $request, string $renewalId): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', Renewal::class);

        $validated = $request->validate([
            'amount'         => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:255',
            'method_type'    => 'required|string|in:visa,mastercard,stripe,paypal',
            'payment_date'   => 'required|string|max:255',
            'billing_cycle'  => 'required|string|max:50',
            'currency'       => 'nullable|string|max:10',
            'usd_amount'     => 'nullable|numeric|min:0',
            'status'         => 'required|string|in:successful,pending',
            'notes'          => 'nullable|string|max:500',
        ]);

        // -------------------------------------------------------------------
        // Resolve source record from prefixed ID
        // -------------------------------------------------------------------
        $type         = 'db';
        $subscription = null;
        $sourcePayment = null;
        $customerName = null;
        $planAsset    = null;
        $prevRenewalDate = null;

        if (str_starts_with($renewalId, 'sub_')) {
            $subId = (int) str_replace('sub_', '', $renewalId);
            $subscription = Subscription::find($subId);
            if (!$subscription) {
                return response()->json(['error' => 'Subscription record not found.'], 404);
            }
            $type         = 'subscription';
            $customerName = $subscription->customer_name;
            $planAsset    = $subscription->plan_name;
            $prevRenewalDate = $subscription->next_billing_date ?? now()->format('M d, Y');

        } elseif (str_starts_with($renewalId, 'pay_')) {
            $payId = (int) str_replace('pay_', '', $renewalId);
            $sourcePayment = Payment::find($payId);
            if (!$sourcePayment) {
                return response()->json(['error' => 'Payment record not found.'], 404);
            }
            $type         = 'payment';
            $customerName = $sourcePayment->customer_name;
            $planAsset    = 'Payment Cycle (' . ucfirst($sourcePayment->billing_cycle ?? 'N/A') . ')';
            $prevRenewalDate = $sourcePayment->end_date ?? $sourcePayment->payment_date ?? now()->format('M d, Y');

        } else {
            // DB Renewal record
            $dbRenewal = Renewal::find((int) $renewalId);
            if (!$dbRenewal) {
                return response()->json(['error' => 'Renewal record not found.'], 404);
            }
            $customerName = $dbRenewal->customer_name;
            $planAsset    = $dbRenewal->plan_asset;
            $prevRenewalDate = $dbRenewal->renewal_date ?? now()->format('M d, Y');
        }

        // -------------------------------------------------------------------
        // Duplicate-renewal prevention
        // -------------------------------------------------------------------
        $paymentDateTs = strtotime($validated['payment_date']);
        if ($paymentDateTs === false) {
            $paymentDateTs = time();
        }
        $paymentMonth = (int) date('m', $paymentDateTs);
        $paymentYear  = (int) date('Y', $paymentDateTs);

        if ($type === 'subscription' && $subscription) {
            // If the subscription is already active and next_billing_date is in the future, it's already renewed
            $nextBillingTs = $subscription->next_billing_date ? strtotime($subscription->next_billing_date) : false;
            if ($nextBillingTs && $nextBillingTs > time()) {
                // Check if there's already a payment for this customer in the same billing month
                $alreadyExists = Payment::where('customer_name', $customerName)
                    ->where('billing_cycle', $validated['billing_cycle'])
                    ->get()
                    ->filter(function ($p) use ($paymentMonth, $paymentYear) {
                        $pts = strtotime($p->payment_date ?? '');
                        return $pts && (int)date('m', $pts) === $paymentMonth && (int)date('Y', $pts) === $paymentYear;
                    })
                    ->isNotEmpty();

                if ($alreadyExists) {
                    return response()->json([
                        'already_renewed' => true,
                        'message' => 'This subscription has already been renewed for the current period.',
                    ], 409);
                }
            }
        }

        if ($type === 'payment') {
            $alreadyExists = Payment::where('customer_name', $customerName)
                ->where('billing_cycle', $validated['billing_cycle'])
                ->where('id', '!=', $sourcePayment->id)
                ->get()
                ->filter(function ($p) use ($paymentMonth, $paymentYear) {
                    $pts = strtotime($p->payment_date ?? '');
                    return $pts && (int)date('m', $pts) === $paymentMonth && (int)date('Y', $pts) === $paymentYear;
                })
                ->isNotEmpty();

            if ($alreadyExists) {
                return response()->json([
                    'already_renewed' => true,
                    'message' => 'This payment has already been renewed for the current period.',
                ], 409);
            }
        }

        // -------------------------------------------------------------------
        // Calculate new end date from billing cycle
        // -------------------------------------------------------------------
        $cycleModel  = BillingCycle::where('code', $validated['billing_cycle'])->first();
        $durationMonths = $cycleModel ? (int) $cycleModel->duration_months : 1;
        $newEndDate  = date('M d, Y', strtotime("+{$durationMonths} months", $paymentDateTs));
        $newRenewalDate = $newEndDate;

        // -------------------------------------------------------------------
        // DB Transaction: create Payment + Invoice, update Subscription
        // -------------------------------------------------------------------
        try {
            DB::transaction(function () use (
                $validated, $customerName, $planAsset, $type, $subscription,
                $newEndDate, $durationMonths, $paymentDateTs, &$newPayment, &$newRenewalDate
            ) {
                $txnId = '#TXN-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 4));
                $invoiceRef = 'INV-REN-' . date('Y') . '-' . strtoupper(substr(uniqid(), -4));

                $cust = \App\Models\Customer::where('name', $customerName)->first();
                $newPayment = Payment::create([
                    'transaction_id'  => $txnId,
                    'customer_id'     => $cust ? $cust->id : null,
                    'customer_name'   => $customerName,
                    'amount'          => $validated['amount'],
                    'payment_method'  => $validated['payment_method'],
                    'method_type'     => $validated['method_type'],
                    'status'          => $validated['status'],
                    'invoice_ref'     => $invoiceRef,
                    'payment_date'    => $validated['payment_date'],
                    'billing_cycle'   => $validated['billing_cycle'],
                    'currency'        => $validated['currency'] ?? 'USD',
                    'usd_amount'      => $validated['usd_amount'] ?? $validated['amount'],
                    'end_date'        => $newEndDate,
                    'payment_context' => 'renewal',
                ]);

                // Auto-create Invoice (mirrors PaymentController::store pattern)
                $invoiceStatus = match ($validated['status']) {
                    'successful' => 'paid',
                    default      => 'pending',
                };
                Invoice::create([
                    'invoice_number' => $invoiceRef,
                    'customer_name'  => $customerName,
                    'amount'         => $validated['amount'],
                    'tax'            => 0,
                    'total'          => $validated['amount'],
                    'invoice_date'   => $validated['payment_date'],
                    'due_date'       => $newEndDate,
                    'status'         => $invoiceStatus,
                    'billing_cycle'  => $validated['billing_cycle'],
                    'currency'       => $validated['currency'] ?? 'USD',
                    'usd_amount'     => $validated['usd_amount'] ?? $validated['amount'],
                ]);

                // Update Subscription if this is a subscription renewal
                if ($type === 'subscription' && $subscription) {
                    $subscription->update([
                        'status'            => 'active',
                        'start_date'        => $validated['payment_date'],
                        'next_billing_date' => $newEndDate,
                        'renewal_date'      => $newEndDate,
                        'billing_cycle'     => $validated['billing_cycle'],
                        'amount'            => $validated['amount'],
                    ]);
                    $newRenewalDate = $newEndDate;
                }
            });

        } catch (\Exception $e) {
            return response()->json(['error' => 'Renewal failed: ' . $e->getMessage()], 500);
        }

        // -------------------------------------------------------------------
        // Audit log — renewal-specific entry (Payment LogsActivity auto-fires
        // on create above; this entry captures the renewal context explicitly)
        // -------------------------------------------------------------------
        $userName = Auth::user()?->name ?? 'System';
        ActivityLog::create([
            'user_name'    => $userName,
            'action'       => 'Renew',
            'model_type'   => 'Payment',
            'reference_id' => $newPayment->transaction_id,
            'message'      => "{$userName} renewed {$type} for {$customerName}",
            'details'      => implode("\n", [
                "Type: {$type}",
                "Plan/Asset: {$planAsset}",
                "Previous renewal: {$prevRenewalDate}",
                "New renewal date: {$newRenewalDate}",
                "Amount: \$" . number_format((float)$validated['amount'], 2),
                "New Payment ID: #{$newPayment->id}",
                "Transaction: {$newPayment->transaction_id}",
            ]),
        ]);

        return response()->json([
            'success'          => true,
            'message'          => "Renewal successful! New payment #{$newPayment->transaction_id} created.",
            'new_payment_id'   => $newPayment->id,
            'new_renewal_date' => $newRenewalDate,
        ]);
    }

    /**
     * Terminate or drop automated schedule records mapping target id strings.
     */
    public function destroy(Renewal $renewal): RedirectResponse
    {
        $renewal->delete();

        return redirect()->route('renewals.index');
    }
}
