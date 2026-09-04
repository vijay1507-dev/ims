<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use App\Http\Requests\StoreSubscriptionRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Subscription::class, 'subscription');
    }

    /**
     * Display a comprehensive listing of subscriptions fed dynamically from relational package catalogs.
     */
    public function index(Request $request): Response
    {
        Subscription::syncAllCustomersWithSubscriptions();
        $query = Subscription::with('subCustomer:id,name');

        // Reactive status filtering
        if ($request->filled('status') && $request->status !== 'All Status') {
            $query->where('status', strtolower($request->status));
        }

        // Reactive plan filtering
        if ($request->filled('plan') && $request->plan !== 'All Plans') {
            $query->where('plan_name', $request->plan);
        }

        // Global subscriber search query
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('customer_name', 'like', "%{$searchTerm}%")
                  ->orWhere('customer_email', 'like', "%{$searchTerm}%");
            });
        }

        $currentYear = date('Y');
        $selectedMonth = $request->input('filter_month', 'All Months');
        $selectedYear = $request->input('filter_year', 'All Years');

        $monthsList = [
            'All Months', 'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        $currentYearInt = (int)date('Y');
        $availableYears = ['All Years'];
        for ($y = $currentYearInt - 10; $y <= $currentYearInt + 10; $y++) {
            $availableYears[] = (string)$y;
        }

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

        $subscriptions = $query->latest()->get()->map(function ($s) use ($currentYear, $formatDateStr) {
            $dateStr = $s->next_billing_date;
            if ($dateStr && str_ends_with($dateStr, '2024')) {
                $dateStr = str_replace('2024', $currentYear, $dateStr);
            }
            if ($dateStr && str_ends_with($dateStr, '2025')) {
                $dateStr = str_replace('2025', $currentYear, $dateStr);
            }
            $dateStr = $formatDateStr($dateStr);

            return [
                'id' => $s->id,
                'customer_name' => $s->customer_name,
                'customer_email' => $s->customer_email,
                'customer_avatar' => $s->customer_avatar ?? 'https://picsum.photos/seed/' . md5($s->customer_name) . '/32/32.jpg',
                'plan_name' => $s->plan_name,
                'billing_cycle' => $s->billing_cycle,
                'start_date' => $s->start_date ?? ($s->created_at ? $s->created_at->format('M d, Y') : now()->format('M d, Y')),
                'next_billing_date' => $dateStr ?? ($s->created_at ? $s->created_at->addMonth()->format('M d, Y') : now()->addMonth()->format('M d, Y')),
                'amount' => $s->formatted_amount,
                'raw_amount' => $s->amount,
                'status' => $s->status,
                'sub_customer_name' => $s->subCustomer?->name,
                'sub_customer_id' => $s->sub_customer_id,
            ];
        });

        if ($selectedMonth !== 'All Months' || $selectedYear !== 'All Years') {
            $subscriptions = $subscriptions->filter(function ($item) use ($selectedMonth, $selectedYear) {
                if (empty($item['start_date'])) {
                    return false;
                }
                $ts = strtotime($item['start_date']);
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

        // Dynamic Calculations mirroring original design metrics exactly
        $totalSubscriptions = Subscription::count();
        $activeSubscriptions = Subscription::where('status', 'active')->count();
        $churnedCount = Subscription::where('status', 'cancelled')->count();
        $totalMrr = Subscription::where('status', 'active')->sum('amount');

        // Dynamic pricing plan metadata loaded straight from user custom schema models
        $packages = SubscriptionPackage::with(['pricing', 'packageFeatures.feature'])->orderBy('sort_order')->get();
        
        $plans = $packages->map(function ($pkg) {
            $pricing = $pkg->pricing->where('billing_cycle', 'monthly')->first() 
                    ?? $pkg->pricing->first();
            
            $priceVal = $pricing ? $pricing->price : 0;
            $interval = ($pricing && $pricing->billing_cycle === 'annual') ? 'yr' : 'mo';

            // Resolve dynamic DB mapping feature tags
            $loadedFeatures = $pkg->packageFeatures->map(function ($pf) {
                $featureName = $pf->feature ? $pf->feature->name : '';
                if (empty($featureName)) return null;

                // 1. Show only available features
                if ($pf->limit_type === 'disabled') return null;

                $display = $featureName;
                $isStaffLogin = stripos($featureName, 'Staff login upto') !== false;

                // Strip any existing literal parentheses or numbers like "(0)" or "(6)" from the base database feature name string
                $display = preg_replace('/\s*\(\s*\d*\s*\)/', '', $display);

                if ($isStaffLogin) {
                    // Show limit_value only for this and don't add braces
                    if ($pf->limit_type === 'limited' && !empty($pf->limit_value)) {
                        $display .= ' ' . $pf->limit_value;
                    } elseif ($pf->limit_type === 'unlimited') {
                        $display .= ' Unlimited';
                    }
                }

                return [
                    'name' => trim($display),
                    'included' => true,
                    'raw_name' => $featureName,
                ];
            })->filter()->values();

            // 4. And this point should be at first.
            // Sort available features so that 'Staff login upto' is always at the top
            $loadedFeatures = $loadedFeatures->sortBy(function ($item) {
                return stripos($item['raw_name'], 'Staff login upto') !== false ? 0 : 1;
            })->values()->map(function ($item) {
                unset($item['raw_name']);
                return $item;
            })->all();

            // Default intuitive pre-configured parameters matching package segments if custom features aren't seeded yet
            if (empty($loadedFeatures)) {
                $code = strtolower($pkg->code ?: $pkg->name);
                $limitVal = (str_contains($code, 'pro') || str_contains($code, 'business')) ? '10' : (str_contains($code, 'enterprise') ? '20' : '6');
                
                $loadedFeatures = [
                    ['name' => "Staff login upto $limitVal", 'included' => true],
                    ['name' => 'Analytics and reporting', 'included' => true],
                    ['name' => 'Chat support', 'included' => true],
                    ['name' => 'Email notification', 'included' => true],
                    ['name' => 'Customize design', 'included' => true],
                ];
            }

            return [
                'name' => $pkg->name,
                'price' => '$' . number_format((float)$priceVal, 0),
                'interval' => $interval,
                'popular' => (bool) $pkg->is_most_popular,
                'subtitle' => $pkg->subtitle,
                'features' => $loadedFeatures,
            ];
        });

        // Fallback safety if packages table has no loaded state mapping
        if ($plans->isEmpty()) {
            $plans = collect([
                [
                    'name' => 'Starter Plan',
                    'price' => '$11',
                    'interval' => 'mo',
                    'popular' => false,
                    'subtitle' => 'Essential features for small teams',
                    'features' => [
                        ['name' => 'Up to 5 Team Members', 'included' => true],
                        ['name' => 'Standard Analytics Core', 'included' => true],
                        ['name' => 'Community Web Support', 'included' => true],
                    ],
                ],
                [
                    'name' => 'Business Plan',
                    'price' => '$25',
                    'interval' => 'mo',
                    'popular' => true,
                    'subtitle' => 'Advanced telemetry and priority queues',
                    'features' => [
                        ['name' => 'Up to 20 Team Members', 'included' => true],
                        ['name' => 'Advanced API Integrations', 'included' => true],
                        ['name' => 'Priority Email Queues', 'included' => true],
                    ],
                ],
                [
                    'name' => 'Enterprise Plan',
                    'price' => '$40',
                    'interval' => 'mo',
                    'popular' => false,
                    'subtitle' => 'Unlimited SLA support and dedicated hosting',
                    'features' => [
                        ['name' => 'Unlimited Team Members', 'included' => true],
                        ['name' => 'Dedicated SLA Guarantees', 'included' => true],
                        ['name' => '24/7 Priority Phone Support', 'included' => true],
                    ],
                ],
            ]);
        }

        return Inertia::render('Subscriptions/Index', [
            'metrics' => [
                'total_subscriptions' => number_format($totalSubscriptions),
                'active_subscriptions' => number_format($activeSubscriptions),
                'churned_count' => number_format($churnedCount),
                'total_mrr' => '$' . number_format($totalMrr),
            ],
            'plans' => $plans,
            'subscriptions' => $subscriptions,
            'availableMonths' => $monthsList,
            'availableYears' => $availableYears,
            'filters' => [
                'status' => $request->status ?? 'All Status',
                'plan' => $request->plan ?? 'All Plans',
                'search' => $request->search ?? '',
                'filter_month' => $selectedMonth,
                'filter_year' => $selectedYear,
                'page' => $request->page ?? 1,
            ],
        ]);
    }

    /**
     * Show the form for creating a new subscription allocation.
     */
    public function create(Request $request): Response
    {
        $packages = SubscriptionPackage::with('pricing')->orderBy('sort_order')->get()->map(function ($pkg) {
            $pricing = $pkg->pricing->where('billing_cycle', 'monthly')->first() ?? $pkg->pricing->first();
            return [
                'name' => $pkg->name,
                'default_amount' => $pricing ? $pricing->price : 0,
            ];
        });

        if ($packages->isEmpty()) {
            $packages = collect([
                ['name' => 'Starter Plan', 'default_amount' => 11.00],
                ['name' => 'Business Plan', 'default_amount' => 25.00],
                ['name' => 'Enterprise Plan', 'default_amount' => 40.00],
                ['name' => 'Custom', 'default_amount' => 55.00],
            ]);
        }

        return Inertia::render('Subscriptions/Create', [
            'plans' => $packages,
            'initial_name' => $request->query('customer_name', ''),
            'initial_email' => $request->query('customer_email', ''),
            'redirect_customer_id' => $request->query('redirect_customer_id', ''),
            'customers' => \App\Models\Customer::with('subCustomers:id,customer_id,name')->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    /**
     * Store a newly created subscription in storage.
     */
    public function store(StoreSubscriptionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['start_date'])) {
            $data['start_date'] = now()->format('M d, Y');
        }

        if (empty($data['next_billing_date'])) {
            $data['next_billing_date'] = now()->addMonth()->format('M d, Y');
        }

        if (empty($data['customer_avatar'])) {
            $seed = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($data['customer_name']));
            $data['customer_avatar'] = 'https://picsum.photos/seed/' . ($seed ?: 'sub') . '/32/32.jpg';
        }

        Subscription::create($data);

        $msg = 'Subscription allocation deployed successfully for ' . $data['customer_name'];

        if ($request->filled('redirect_customer_id')) {
            return redirect()->route('customers.edit', $request->redirect_customer_id)->with('success', $msg);
        }

        return redirect()->route('subscriptions.index')->with('success', $msg);
    }

    /**
     * Show the form for editing the specified subscription record.
     */
    public function edit(Request $request, Subscription $subscription): Response
    {
        $packages = SubscriptionPackage::with('pricing')->orderBy('sort_order')->get()->map(function ($pkg) {
            $pricing = $pkg->pricing->where('billing_cycle', 'monthly')->first() ?? $pkg->pricing->first();
            return [
                'name' => $pkg->name,
                'default_amount' => $pricing ? $pricing->price : 0,
            ];
        });

        if ($packages->isEmpty()) {
            $packages = collect([
                ['name' => 'Starter Plan', 'default_amount' => 11.00],
                ['name' => 'Business Plan', 'default_amount' => 25.00],
                ['name' => 'Enterprise Plan', 'default_amount' => 40.00],
                ['name' => 'Custom', 'default_amount' => 55.00],
            ]);
        }

        return Inertia::render('Subscriptions/Edit', [
            'subscription' => [
                'id' => $subscription->id,
                'customer_name' => $subscription->customer_name,
                'customer_email' => $subscription->customer_email,
                'plan_name' => $subscription->plan_name,
                'billing_cycle' => $subscription->billing_cycle,
                'start_date' => $subscription->start_date ?? ($subscription->created_at ? $subscription->created_at->format('M d, Y') : now()->format('M d, Y')),
                'next_billing_date' => $subscription->next_billing_date,
                'amount' => $subscription->amount,
                'status' => $subscription->status,
                'sub_customer_id' => $subscription->sub_customer_id,
            ],
            'plans' => $packages,
            'returnPage' => (int) $request->input('page', 1),
            'customers' => \App\Models\Customer::with('subCustomers:id,customer_id,name')->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    /**
     * Update the specified subscription record in storage.
     */
    public function update(StoreSubscriptionRequest $request, Subscription $subscription): RedirectResponse
    {
        $subscription->update($request->validated());

        $page = $request->input('page', 1);
        return redirect()->route('subscriptions.index', ['page' => $page])->with('success', 'Subscription updated successfully.');
    }

    /**
     * Remove the specified subscription record from storage.
     */
    public function destroy(Subscription $subscription): RedirectResponse
    {
        $subscription->delete();

        return redirect()->route('subscriptions.index');
    }
}
