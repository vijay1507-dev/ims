<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Customer;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use App\Models\Payment;
use App\Models\Inventory;
use App\Models\Invoice;
use App\Http\Requests\StoreCustomerRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Customer::class);
    }

    /**
     * Display a dynamic listing of enterprise client accounts.
     */
    public function index(Request $request): Response
    {
        Subscription::syncAllCustomersWithSubscriptions();
        $query = Customer::query();

        // Reactive status filtering
        if ($request->filled('status') && $request->status !== 'All Status') {
            $query->where('status', strtolower($request->status));
        }

        // Reactive plan filtering
        if ($request->filled('plan') && $request->plan !== 'All Plans') {
            $query->where('plan', $request->plan);
        }

        // Global text pattern search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        $query->withCount('subCustomers');

        $customers = $query->latest()->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'contact_name' => $c->contact_name,
                'email' => $c->email,
                'phone' => $c->phone,
                'role' => $c->role,
                'avatar' => $c->avatar ?? 'https://picsum.photos/seed/' . md5($c->name) . '/32/32.jpg',
                'plan' => $c->plan,
                'status' => $c->status,
                'mrr' => $c->formatted_mrr,
                'raw_mrr' => $c->mrr,
                'joined_date' => $c->joined_date ? date('M d, Y', strtotime($c->joined_date)) : $c->created_at->format('M d, Y'),
                'sub_customers_count' => $c->sub_customers_count,
            ];
        });

        // Dynamic Counters
        $totalCustomers = Customer::count();
        $activeAccounts = Customer::where('status', 'active')->count();
        $trialAccounts = Customer::where('status', 'trial')->count();
        $inactiveAccounts = Customer::where('status', 'inactive')->count();

        // Fetch dynamic plans from DB
        $plans = SubscriptionPackage::orderBy('sort_order')->pluck('name');

        return Inertia::render('Customers/Index', [
            'metrics' => [
                'total_customers' => number_format($totalCustomers),
                'active_accounts' => number_format($activeAccounts),
                'trial_accounts' => number_format($trialAccounts),
                'inactive_accounts' => number_format($inactiveAccounts),
            ],
            'customers' => $customers,
            'plans' => $plans,
            'filters' => [
                'status' => $request->status ?? 'All Status',
                'plan' => $request->plan ?? 'All Plans',
                'search' => $request->search ?? '',
                'page' => $request->page ?? 1,
            ],
        ]);
    }

    /**
     * Display the dynamic Customer Profile Page mirroring explicit prototype layouts.
     */
    public function show(Customer $customer): Response
    {
        // Fetch specific dynamic client active plans
        $subscriptions = Subscription::where(function($q) use ($customer) {
            $q->where('customer_name', $customer->name)
              ->orWhere('customer_name', $customer->contact_name);
        })->latest()->get();

        // Fetch dynamic Billing History traces
        $payments = Payment::where(function($q) use ($customer) {
            $q->where('customer_name', $customer->name)
              ->orWhere('customer_name', $customer->contact_name);
        })->orderBy('id', 'desc')->get();

        // Fetch dynamic Assigned inventory/devices
        $devices = Inventory::where(function($q) use ($customer) {
            $q->where('customer_name', $customer->name)
              ->orWhere('customer_name', $customer->contact_name);
        })->latest()->get();

        // Derive dynamic activities from database records
        $activities = collect();

        foreach ($payments as $payment) {
            $activities->push([
                'id' => 'pay_' . $payment->id,
                'title' => 'Payment Cleared Successfully',
                'date' => ($payment->payment_date ?: $payment->created_at->setTimezone('Asia/Kolkata')->format('M d, Y')) . ' at ' . $payment->created_at->setTimezone('Asia/Kolkata')->format('h:i A'),
                'color' => 'border-green-500 bg-green-50 text-green-600',
                'icon' => 'fas fa-check',
                'raw_date' => $payment->created_at,
            ]);
        }

        foreach ($devices as $device) {
            $activities->push([
                'id' => 'dev_' . $device->id,
                'title' => 'Hardware Asset Provisioned',
                'date' => ($device->assigned_date ?: $device->created_at->setTimezone('Asia/Kolkata')->format('M d, Y')) . ' at ' . $device->created_at->setTimezone('Asia/Kolkata')->format('h:i A'),
                'color' => 'border-blue-500 bg-blue-50 text-blue-600',
                'icon' => 'fas fa-box',
                'raw_date' => $device->created_at,
            ]);
        }

        foreach ($subscriptions as $sub) {
            $activities->push([
                'id' => 'sub_' . $sub->id,
                'title' => 'Subscription Ledger Updated',
                'date' => $sub->created_at->setTimezone('Asia/Kolkata')->format('M d, Y') . ' at ' . $sub->created_at->setTimezone('Asia/Kolkata')->format('h:i A'),
                'color' => 'border-purple-500 bg-purple-50 text-purple-600',
                'icon' => 'fas fa-sync',
                'raw_date' => $sub->created_at,
            ]);
        }

        $activities = $activities->sortByDesc('raw_date')->values();

        $subCustomers = $customer->subCustomers()->withCount('payments')->orderBy('name')->get()->map(function ($sc) {
            return [
                'id' => $sc->id,
                'name' => $sc->name,
                'email' => $sc->email,
                'phone' => $sc->phone,
                'address' => $sc->address,
                'status' => $sc->status,
                'payments_count' => $sc->payments_count,
                'revenue' => $sc->payments()->where('status', 'successful')->sum('amount'),
                'outstanding' => $sc->payments()->where('status', 'pending')->sum('amount'),
            ];
        });

        return Inertia::render('Customers/Show', [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'avatar' => $customer->avatar ?? 'https://picsum.photos/seed/' . md5($customer->name) . '/80/80.jpg',
                'plan' => $customer->plan,
                'status' => $customer->status,
                'mrr' => $customer->formatted_mrr,
                'raw_mrr' => $customer->mrr,
                'joined_date' => $customer->joined_date ? date('M Y', strtotime($customer->joined_date)) : $customer->created_at->format('M Y'),
            ],
            'subscriptions' => $subscriptions,
            'payments' => $payments,
            'devices' => $devices,
            'activities' => $activities,
            'subCustomers' => $subCustomers,
            'revenueTrend' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
                'data' => [
                    (float)($customer->mrr ?: 2450),
                    (float)($customer->mrr ?: 2450),
                    (float)($customer->mrr ?: 2450),
                    (float)($customer->mrr ?: 2450),
                    (float)($customer->mrr ?: 2450),
                ]
            ],
            'billing_details' => Subscription::where('customer_email', $customer->email)->latest()->first() ? [
                'plan_name' => Subscription::where('customer_email', $customer->email)->latest()->first()->plan_name,
                'billing_cycle' => Subscription::where('customer_email', $customer->email)->latest()->first()->billing_cycle,
                'status' => Subscription::where('customer_email', $customer->email)->latest()->first()->status,
                'next_billing_date' => Subscription::where('customer_email', $customer->email)->latest()->first()->next_billing_date,
                'renewal_date' => Subscription::where('customer_email', $customer->email)->latest()->first()->renewal_date,
                'auto_renewal' => Subscription::where('customer_email', $customer->email)->latest()->first()->auto_renewal ? 'Enabled' : 'Disabled',
            ] : null,
        ]);
    }

    /**
     * Show the form for creating a new customer account.
     */
    public function create(): Response
    {
        $countries = Country::orderBy('name')->pluck('name');
        $industries = [
            'Banking & Finance', 'Healthcare', 'Retail', 'Government', 
            'Telecom', 'Education', 'Automotive', 'Transportation', 'Other'
        ];
        return Inertia::render('Customers/Create', [
            'countries' => $countries,
            'industries' => $industries,
            'mainCustomers' => Customer::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Show the form for editing the specified customer account alongside live module bindings.
     */
    public function edit(Request $request, Customer $customer): Response
    {
        // Query persistent inventory records directly belonging to this client
        $devices = Inventory::where(function($q) use ($customer) {
            $q->where('customer_name', $customer->name)
              ->orWhere('customer_name', $customer->contact_name);
        })->latest()->get()->map(function ($ast) {
            return [
                'id' => $ast->id,
                'asset_id' => $ast->asset_id,
                'category' => $ast->category,
                'type_name' => $ast->type_name,
                'name_model' => $ast->name_model,
                'serial_license' => $ast->serial_license,
                'status' => $ast->status,
                'warranty_expiry' => $ast->warranty_expiry,
            ];
        });

        // Query persistent taxation invoice structures directly belonging to this client
        $invoices = Invoice::where(function($q) use ($customer) {
            $q->where('customer_name', $customer->name)
              ->orWhere('customer_name', $customer->contact_name);
        })->latest()->get()->map(function ($inv) {
            return [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'amount' => $inv->formatted_amount,
                'tax' => $inv->formatted_tax,
                'total' => $inv->formatted_total,
                'status' => $inv->status,
                'due_date' => $inv->due_date,
            ];
        });

        $countries = Country::orderBy('name')->pluck('name');
        $industries = [
            'Banking & Finance', 'Healthcare', 'Retail', 'Government', 
            'Telecom', 'Education', 'Automotive', 'Transportation', 'Other'
        ];

        $subCustomers = $customer->subCustomers()->withCount('payments')->orderBy('name')->get()->map(function ($sc) {
            return [
                'id' => $sc->id,
                'name' => $sc->name,
                'email' => $sc->email,
                'status' => $sc->status,
                'payments_count' => $sc->payments_count,
            ];
        });

        return Inertia::render('Customers/Edit', [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'contact_name' => $customer->contact_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'role' => $customer->role,
                'industry' => $customer->industry,
                'country' => $customer->country,
                'message' => $customer->message,
                'joined_date' => $customer->joined_date ? date('Y-m-d', strtotime($customer->joined_date)) : $customer->created_at->format('Y-m-d'),
            ],
            'devices' => $devices,
            'invoices' => $invoices,
            'countries' => $countries,
            'industries' => $industries,
            'subCustomers' => $subCustomers,
            'returnPage' => (int) $request->input('page', 1),
        ]);
    }

    /**
     * Real-time nested mutator handling dynamic generation of a new customer hardware inventory object.
     */
    public function storeInventory(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'category' => 'required|string|in:kiosk,tv,tablet,printer,license,desktop,display,mobile',
            'name_model' => 'required|string|max:255',
            'serial_license' => 'nullable|string|max:255',
            'status' => 'required|string|in:active,maintenance,inactive,retired',
            'assigned_date' => 'nullable|string|max:255',
            'warranty_expiry' => 'nullable|string|max:255',
        ]);

        $types = [
            'kiosk' => 'Kiosk Terminal',
            'tv' => 'Smart TV Display',
            'tablet' => 'Tablet Device',
            'printer' => 'Network Printer',
            'license' => 'License Key',
            'desktop' => 'Workstation',
            'display' => 'Display Unit',
            'mobile' => 'Mobile Unit',
        ];
        $validated['type_name'] = $types[$validated['category']] ?? 'Asset';
        $validated['asset_id'] = 'AST-' . strtoupper(substr(md5(uniqid()), 0, 4));
        $validated['customer_name'] = $customer->name;

        if (empty($validated['assigned_date'])) {
            $validated['assigned_date'] = now()->format('M d, Y');
        }
        if (empty($validated['warranty_expiry'])) {
            $validated['warranty_expiry'] = now()->addYears(2)->format('M d, Y');
        }

        Inventory::create($validated);

        return redirect()->back()->with('success', 'Inventory asset provisioned successfully.');
    }

    /**
     * Real-time nested mutator handling immediate compilation of a customer dynamic taxation invoice.
     */
    public function storeInvoice(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'status' => 'required|string|in:paid,pending,overdue,draft',
            'invoice_date' => 'nullable|string|max:255',
            'due_date' => 'nullable|string|max:255',
        ]);

        $validated['customer_name'] = $customer->name;
        $validated['total'] = $validated['amount'] + $validated['tax'];
        $validated['invoice_number'] = 'INV-' . date('Y') . '-' . strtoupper(substr(uniqid(), -3));

        if (empty($validated['invoice_date'])) {
            $validated['invoice_date'] = now()->format('M d, Y');
        }
        if (empty($validated['due_date'])) {
            $validated['due_date'] = now()->addDays(30)->format('M d, Y');
        }

        Invoice::create($validated);

        return redirect()->back()->with('success', 'Invoice generated successfully.');
    }

    /**
     * Store a newly created customer account in storage.
     */
    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['joined_date'])) {
            $data['joined_date'] = now()->format('Y-m-d');
        }

        if (empty($data['avatar'])) {
            $seed = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($data['name']));
            $data['avatar'] = 'https://picsum.photos/seed/' . ($seed ?: 'user') . '/32/32.jpg';
        }

        Customer::create($data);

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    /**
     * Update the specified customer account in storage.
     */
    public function update(StoreCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        $page = $request->input('page', 1);
        return redirect()->route('customers.index', ['page' => $page])->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer account from storage.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->subCustomers()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete this customer: it still has sub-customers/locations. Remove those first.');
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
}
