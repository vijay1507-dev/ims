<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\SubCustomer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Payment::class, 'payment');
    }

    /**
     * Display a listing of recorded transactions supporting dynamic live searching and aggregate KPI evaluations.
     */
    public function index(Request $request): Response
    {
        $query = Payment::query();

        // Reactive status filtering
        if ($request->filled('status') && $request->status !== 'All Status') {
            $query->where('status', strtolower($request->status));
        }

        // Billing cycle filter
        if ($request->filled('billing_cycle') && $request->billing_cycle !== 'All Cycles') {
            $query->where('billing_cycle', $request->billing_cycle);
        }

        // Search inputs (including closed_by)
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('transaction_id', 'like', "%{$searchTerm}%")
                  ->orWhere('customer_name', 'like', "%{$searchTerm}%")
                  ->orWhere('invoice_ref', 'like', "%{$searchTerm}%")
                  ->orWhere('closed_by', 'like', "%{$searchTerm}%");
            });
        }

        // Customer filter
        if ($request->filled('customer_id') && $request->customer_id !== 'All Customers') {
            $query->where('customer_id', $request->customer_id);
        }

        // Sub-customer/location filter
        if ($request->filled('sub_customer_id') && $request->sub_customer_id !== 'All Locations') {
            $query->where('sub_customer_id', $request->sub_customer_id);
        }

        // Closed By filter
        if ($request->filled('closed_by') && $request->closed_by !== 'All Representatives') {
            $query->where('closed_by', $request->closed_by);
        }

        $query->with('subCustomer:id,name');

        $selectedMonth = $request->input('filter_month', 'All Months');
        $selectedYear = $request->input('filter_year', 'All Years');

        $monthsList = ['All Months', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $availableYears = ['All Years'];
        for ($y = (int)date('Y') - 10; $y <= (int)date('Y') + 10; $y++) {
            $availableYears[] = (string)$y;
        }

        $payments = $query->latest()->get()->map(function ($p) {
            $dateStr = $p->payment_date ?? $p->created_at->format('Y-m-d');
            $ts = strtotime($dateStr);
            if ($ts === false) {
                if (preg_match('/^(\d{1,2})[-|\/](\d{1,2})[-|\/](\d{4})$/', trim($dateStr), $matches)) {
                    $ts = mktime(0, 0, 0, (int)$matches[2], (int)$matches[1], (int)$matches[3]);
                }
            }
            $formattedDate = $ts !== false ? date('M d, Y', $ts) : $dateStr;
            
            // Evaluate Due Date dynamically based on standard payment term buffers
            $dueDateStr = date('M d, Y', strtotime(($ts !== false ? date('Y-m-d', $ts) : date('Y-m-d')) . ' + 14 days'));

            $endDateStr = $p->end_date;
            if (!$endDateStr && $p->billing_cycle) {
                $dateStrForEnd = $p->payment_date ?? $p->created_at->format('Y-m-d');
                $tsForEnd = strtotime($dateStrForEnd);
                if ($tsForEnd === false) {
                    if (preg_match('/^(\d{1,2})[-|\/](\d{1,2})[-|\/](\d{4})$/', trim($dateStrForEnd), $matches)) {
                        $tsForEnd = mktime(0, 0, 0, (int)$matches[2], (int)$matches[1], (int)$matches[3]);
                    }
                }
                if ($tsForEnd !== false) {
                    $cycle = \App\Models\BillingCycle::where('code', $p->billing_cycle)->first();
                    $months = $cycle ? $cycle->duration_months : 1;
                    $endDateStr = date('M d, Y', strtotime("+{$months} months", $tsForEnd));
                }
            }
            $formattedEndDate = null;
            if ($endDateStr) {
                $endTs = strtotime($endDateStr);
                if ($endTs === false) {
                    if (preg_match('/^(\d{1,2})[-|\/](\d{1,2})[-|\/](\d{4})$/', trim($endDateStr), $matches)) {
                        $endTs = mktime(0, 0, 0, (int)$matches[2], (int)$matches[1], (int)$matches[3]);
                    }
                }
                $formattedEndDate = $endTs !== false ? date('M d, Y', $endTs) : $endDateStr;
            }

            return [
                'id' => $p->id,
                'transaction_id' => $p->transaction_id,
                'customer_name' => $p->customer_name,
                'customer_id' => $p->customer_id,
                'sub_customer_name' => $p->subCustomer?->name,
                'sub_customer_id' => $p->sub_customer_id,
                'amount' => $p->formatted_amount,
                'raw_amount' => $p->amount,
                'payment_method' => $p->payment_method,
                'method_type' => $p->method_type,
                'status' => $p->status,
                'invoice_ref' => $p->invoice_ref ?? 'INV-' . date('Y') . '-' . str_pad($p->id, 3, '0', STR_PAD_LEFT),
                'payment_date' => $formattedDate,
                'due_date' => $dueDateStr,
                'billing_cycle' => $p->billing_cycle,
                'currency' => $p->currency ?? 'USD',
                'usd_amount' => $p->usd_amount ?? 0.00,
                'end_date' => $formattedEndDate,
                'closed_by' => $p->closed_by,
            ];
        });

        // Filter payments by Month and Year
        if ($selectedMonth !== 'All Months' || $selectedYear !== 'All Years') {
            $payments = $payments->filter(function ($item) use ($selectedMonth, $selectedYear) {
                if (empty($item['payment_date'])) {
                    return false;
                }
                $ts = strtotime($item['payment_date']);
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

        // Compute dynamic aggregate telemetry balancing live sums straight from database state
        $totalPayments = $payments->count();
        $successfulCount = $payments->where('status', 'successful')->count();
        $receivedSum = $payments->where('status', 'successful')->sum('usd_amount');
        $pendingSum = $payments->where('status', 'pending')->sum('usd_amount');

        // Fetch all unique representative names for filter dropdown
        $representatives = Payment::whereNotNull('closed_by')
            ->where('closed_by', '!=', '')
            ->distinct()
            ->pluck('closed_by')
            ->sort()
            ->values();

        return Inertia::render('Payments/Index', [
            'metrics' => [
                'total_payments' => number_format($totalPayments),
                'successful_payments' => number_format($successfulCount),
                'received_payments' => '$' . number_format($receivedSum, 2),
                'pending_payments' => '$' . number_format($pendingSum, 2),
            ],
            'payments' => $payments,
            'availableMonths' => $monthsList,
            'availableYears' => $availableYears,
            'customers' => \App\Models\Customer::orderBy('name')->get(['id', 'name']),
            'subCustomers' => SubCustomer::orderBy('name')->get(['id', 'name', 'customer_id']),
            'representatives' => $representatives,
            'filters' => [
                'status' => $request->status ?? 'All Status',
                'search' => $request->search ?? '',
                'filter_month' => $selectedMonth,
                'filter_year' => $selectedYear,
                'billing_cycle' => $request->billing_cycle ?? 'All Cycles',
                'customer_id' => $request->customer_id ?? 'All Customers',
                'sub_customer_id' => $request->sub_customer_id ?? 'All Locations',
                'closed_by' => $request->closed_by ?? 'All Representatives',
                'page' => $request->page ?? 1,
            ],
        ]);
    }

    /**
     * Dispatch automated transactional refund state mutations.
     */
    public function refund(Payment $payment): RedirectResponse
    {
        $payment->update(['status' => 'refunded']);

        return redirect()->route('payments.index')->with('success', 'Transaction successfully refunded.');
    }

    /**
     * Generate dynamic downloadable document string stream representing official compiled PDF records.
     */
    public function downloadPdf(Payment $payment)
    {
        $invoiceNo = $payment->invoice_ref ?? 'INV-GEN-' . $payment->id;
        $content = "===================================================\n";
        $content .= "             OFFICIAL TAX INVOICE DOCUMENT         \n";
        $content .= "===================================================\n\n";
        $content .= "Invoice Ref:     " . $invoiceNo . "\n";
        $content .= "Transaction Key: " . $payment->transaction_id . "\n";
        $content .= "Customer Entity: " . $payment->customer_name . "\n";
        $content .= "Clearing Date:   " . ($payment->payment_date ?: now()->format('M d, Y')) . "\n";
        $content .= "Gateway Stream:  " . $payment->payment_method . "\n";
        $content .= "Authorized Sum:  $" . number_format((float)$payment->amount, 2) . "\n";
        $content .= "Status Marker:   " . strtoupper($payment->status) . "\n\n";
        $content .= "Thank you for conducting cloud business infrastructure operations.\n";
        $content .= "===================================================\n";

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, strtolower($invoiceNo) . '.txt', [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . strtolower($invoiceNo) . '.txt"',
        ]);
    }

    /**
     * Show the form for recording a new transactional record.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Payments/Create', [
            'initial_customer' => $request->query('customer', ''),
            'redirect_customer_id' => $request->query('redirect_customer_id', ''),
            'customers' => \App\Models\Customer::with('subCustomers:id,customer_id,name')->orderBy('name')->get(['id', 'name', 'country']),
            'paymentChannels' => \App\Models\PaymentChannel::where('status', 'active')->orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    /**
     * Store a newly recorded transaction in database storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:255',
            'method_type' => [
                'required',
                'string',
                \Illuminate\Validation\Rule::exists('payment_channels', 'code')->where(function ($q) {
                    $q->where('status', 'active');
                })
            ],
            'status' => 'required|string|in:successful,failed,pending,refunded',
            'invoice_ref' => 'nullable|string|max:255',
            'payment_date' => 'nullable|string|max:255',
            'redirect_customer_id' => 'nullable|integer',
            'billing_cycle' => 'nullable|string|in:monthly,annual,two_months,half_yearly,quarterly,evently',
            'currency' => 'nullable|string|max:10',
            'usd_amount' => 'nullable|numeric|min:0',
            'end_date' => 'nullable|string|max:255',
            'sub_customer_id' => 'nullable|integer|exists:sub_customers,id',
            'closed_by' => 'nullable|string|max:255',
        ]);

        // Generate standard transactional sequence identifiers
        $validated['transaction_id'] = '#TXN-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 4));
        if (empty($validated['payment_date'])) {
            $validated['payment_date'] = now()->format('M d, Y');
        }

        if (empty($validated['invoice_ref'])) {
            $validated['invoice_ref'] = 'INV-' . date('Y') . '-' . strtoupper(substr(uniqid(), -3));
        }

        if (empty($validated['end_date']) && !empty($validated['billing_cycle'])) {
            $startDate = $validated['payment_date'];
            $ts = strtotime($startDate);
            if ($ts !== false) {
                $cycle = \App\Models\BillingCycle::where('code', $validated['billing_cycle'])->first();
                $months = $cycle ? $cycle->duration_months : 1;
                $validated['end_date'] = date('M d, Y', strtotime("+{$months} months", $ts));
            }
        }

        // Resolve customer_id and determine payment_context (new vs renewal)
        $cust = \App\Models\Customer::where('name', $validated['customer_name'])->first();
        $validated['customer_id'] = $cust ? $cust->id : null;

        if (!empty($validated['sub_customer_id'])) {
            $subCustomer = SubCustomer::find($validated['sub_customer_id']);
            if (!$subCustomer || !$cust || $subCustomer->customer_id !== $cust->id) {
                return redirect()->back()->withErrors([
                    'sub_customer_id' => 'Selected location does not belong to this customer.',
                ])->withInput();
            }
        }

        $isRenewal = false;
        if ($cust) {
            $isRenewal = Payment::where('customer_id', $cust->id)
                ->where('status', 'successful')
                ->exists();
        }
        $validated['payment_context'] = $isRenewal ? 'renewal' : 'new';

        $payment = Payment::create($validated);

        // Automatically create associated Invoice record for all payments
        $invoiceStatus = 'pending';
        if ($payment->status === 'successful') {
            $invoiceStatus = 'paid';
        } elseif ($payment->status === 'failed' || $payment->status === 'refunded') {
            $invoiceStatus = 'draft';
        }

        $invoiceCustomerName = $payment->customer_name;
        if ($payment->sub_customer_id) {
            $subCustomer = SubCustomer::find($payment->sub_customer_id);
            if ($subCustomer) {
                $invoiceCustomerName = $payment->customer_name . ' - ' . $subCustomer->name;
            }
        }

        \App\Models\Invoice::create([
            'invoice_number' => $payment->invoice_ref,
            'customer_name' => $invoiceCustomerName,
            'amount' => $payment->amount,
            'tax' => 0,
            'total' => $payment->amount,
            'invoice_date' => $payment->payment_date,
            'due_date' => date('M d, Y', strtotime($payment->payment_date . ' + 14 days')),
            'status' => $invoiceStatus,
            'billing_cycle' => $payment->billing_cycle,
            'currency' => $payment->currency ?? 'USD',
            'usd_amount' => $payment->usd_amount ?? 0.00,
            'closed_by' => $payment->closed_by,
        ]);

        if ($request->filled('redirect_customer_id')) {
            return redirect()->route('customers.edit', $request->redirect_customer_id)->with('success', 'Payment recorded and linked to customer successfully.');
        }

        return redirect()->route('payments.index')->with('success', 'Payment recorded successfully.');
    }

    /**
     * Show the form for editing the specified payment transaction.
     */
    public function edit(Request $request, Payment $payment): Response
    {
        return Inertia::render('Payments/Edit', [
            'payment' => [
                'id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'customer_name' => $payment->customer_name,
                'customer_id' => $payment->customer_id,
                'sub_customer_id' => $payment->sub_customer_id,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'method_type' => $payment->method_type,
                'status' => $payment->status,
                'invoice_ref' => $payment->invoice_ref,
                'payment_date' => $payment->payment_date,
                'billing_cycle' => $payment->billing_cycle,
                'currency' => $payment->currency ?? 'USD',
                'usd_amount' => $payment->usd_amount ?? 0.00,
                'end_date' => $payment->end_date,
                'closed_by' => $payment->closed_by,
            ],
            'customers' => \App\Models\Customer::with('subCustomers:id,customer_id,name')->orderBy('name')->get(['id', 'name', 'country']),
            'paymentChannels' => \App\Models\PaymentChannel::where('status', 'active')->orderBy('name')->get(['id', 'name', 'code']),
            'returnPage' => (int) $request->input('page', 1),
        ]);
    }

    /**
     * Update the specified payment transaction in database storage.
     */
    public function update(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:255',
            'method_type' => [
                'required',
                'string',
                \Illuminate\Validation\Rule::exists('payment_channels', 'code')->where(function ($q) {
                    $q->where('status', 'active');
                })
            ],
            'status' => 'required|string|in:successful,failed,pending,refunded',
            'invoice_ref' => 'nullable|string|max:255',
            'payment_date' => 'nullable|string|max:255',
            'billing_cycle' => 'nullable|string|in:monthly,annual,two_months,half_yearly,quarterly,evently',
            'currency' => 'nullable|string|max:10',
            'usd_amount' => 'nullable|numeric|min:0',
            'end_date' => 'nullable|string|max:255',
            'sub_customer_id' => 'nullable|integer|exists:sub_customers,id',
            'closed_by' => 'nullable|string|max:255',
        ]);

        $cust = \App\Models\Customer::where('name', $validated['customer_name'])->first();
        $validated['customer_id'] = $cust ? $cust->id : $payment->customer_id;

        if (!empty($validated['sub_customer_id'])) {
            $subCustomer = SubCustomer::find($validated['sub_customer_id']);
            if (!$subCustomer || !$cust || $subCustomer->customer_id !== $cust->id) {
                return redirect()->back()->withErrors([
                    'sub_customer_id' => 'Selected location does not belong to this customer.',
                ])->withInput();
            }
        }

        if (empty($validated['end_date']) && !empty($validated['billing_cycle'])) {
            $startDate = !empty($validated['payment_date']) ? $validated['payment_date'] : now()->format('Y-m-d');
            $ts = strtotime($startDate);
            if ($ts !== false) {
                $cycle = \App\Models\BillingCycle::where('code', $validated['billing_cycle'])->first();
                $months = $cycle ? $cycle->duration_months : 1;
                $validated['end_date'] = date('M d, Y', strtotime("+{$months} months", $ts));
            }
        }

        $payment->update($validated);

        $page = $request->input('page', 1);
        return redirect()->route('payments.index', ['page' => $page])->with('success', 'Payment updated successfully.');
    }

    /**
     * Remove the specified payment transaction from database storage.
     */
    public function destroy(Payment $payment): RedirectResponse
    {
        $payment->delete();

        return redirect()->route('payments.index')->with('success', 'Payment deleted successfully.');
    }

    public function exchangeRate(Request $request)
    {
        $from = $request->input('from', 'INR');
        $to = $request->input('to', 'USD');
        $dateParam = $request->input('date');

        if ($from === $to) {
            return response()->json(['rates' => [$to => 1.00]]);
        }

        try {
            $conv = \AmrShawky\Currency\Facade\Currency::convert()
                ->from($from)
                ->to($to);

            if (!empty($dateParam)) {
                $timestamp = strtotime($dateParam);
                if ($timestamp !== false) {
                    $conv = $conv->date(date('Y-m-d', $timestamp));
                }
            }

            $rate = $conv->get();
            if ($rate !== null && $rate > 0) {
                return response()->json([
                    'rates' => [$to => $rate],
                ]);
            }
        } catch (\Exception $e) {
            // Fallback handled below
        }

        // Return a fallback rates response instead of 502 so the UI does not fail
        $exchangeRatesFallback = [
            'SGD' => 0.74,
            'USD' => 1.00,
            'INR' => 0.012,
            'AED' => 0.27,
            'EUR' => 1.10,
            'GBP' => 1.30,
            'AUD' => 0.65,
            'CAD' => 0.74,
        ];
        
        $fromUsdRate = $exchangeRatesFallback[$from] ?? 1.00;
        $toUsdRate = $exchangeRatesFallback[$to] ?? 1.00;
        $rate = $fromUsdRate / $toUsdRate;

        return response()->json([
            'rates' => [$to => $rate],
            'fallback' => true
        ]);
    }
}
