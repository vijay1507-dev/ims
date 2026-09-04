<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Invoice::class, 'invoice');
    }

    /**
     * Display a dynamic overview listing customer invoice parameters accompanied by absolute filtered statistics.
     */
    public function index(Request $request): Response
    {
        $query = Invoice::query();

        // Reactive status routing filter
        if ($request->filled('status') && $request->status !== 'All Status') {
            $query->where('status', strtolower($request->status));
        }

        // Live text searching support
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('invoice_number', 'like', "%{$searchTerm}%")
                  ->orWhere('customer_name', 'like', "%{$searchTerm}%");
            });
        }

        $selectedMonth = $request->input('filter_month', 'All Months');
        $selectedYear = $request->input('filter_year', 'All Years');
        $selectedCycle = $request->input('billing_cycle', 'All Cycles');

        $monthsList = ['All Months', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $availableYears = ['All Years'];
        for ($y = (int)date('Y') - 10; $y <= (int)date('Y') + 10; $y++) {
            $availableYears[] = (string)$y;
        }
        $dbCycles = \Schema::hasTable('billing_cycles') 
            ? \App\Models\BillingCycle::orderBy('name')->pluck('name')->toArray() 
            : [];
        $cyclesList = array_merge(['All Cycles'], $dbCycles);

        $invoices = $query->latest()->get()->map(function ($inv) {
            // Fetch customer country dynamically with robust name matching
            $name = trim($inv->customer_name);
            $customer = \App\Models\Customer::where(function($q) use ($name) {
                $q->where('name', $name)
                  ->orWhere('contact_name', $name);
            })->first();
            
            // Fallback to partial match if exact match fails
            if (!$customer) {
                $customer = \App\Models\Customer::where(function($q) use ($name) {
                    $q->where('name', 'like', "%{$name}%")
                      ->orWhere('contact_name', 'like', "%{$name}%");
                })->first();
            }
            
            $resolvedAddress = ($customer && $customer->country) ? $customer->country : 'Global Region';

            return [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'customer_name' => $inv->customer_name,
                'billing_address' => $resolvedAddress,
                'amount' => $inv->formatted_amount,
                'tax' => $inv->formatted_tax,
                'total' => $inv->formatted_total,
                'raw_amount' => $inv->amount,
                'raw_tax' => $inv->tax,
                'raw_total' => $inv->total,
                'invoice_date' => $inv->invoice_date ?? $inv->created_at->format('M d, Y'),
                'due_date' => $inv->due_date ?? $inv->created_at->addDays(30)->format('M d, Y'),
                'status' => $inv->status,
                'billing_cycle' => $inv->billing_cycle,
                'currency' => $inv->currency ?? 'USD',
                'usd_amount' => $inv->usd_amount ?? 0.00,
                'closed_by' => $inv->closed_by,
            ];
        });

        // Filter invoices by Month, Year, and Billing Cycle
        if ($selectedMonth !== 'All Months' || $selectedYear !== 'All Years' || $selectedCycle !== 'All Cycles') {
            $invoices = $invoices->filter(function ($item) use ($selectedMonth, $selectedYear, $selectedCycle) {
                if ($selectedMonth !== 'All Months' || $selectedYear !== 'All Years') {
                    if (empty($item['invoice_date'])) {
                        return false;
                    }
                    $ts = strtotime($item['invoice_date']);
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
                }

                if ($selectedCycle !== 'All Cycles') {
                    $itemCycle = strtolower(str_replace(' ', '_', $item['billing_cycle']));
                    $compareCycle = strtolower(str_replace(' ', '_', $selectedCycle));
                    if ($compareCycle === 'yearly') $compareCycle = 'annual';
                    if ($itemCycle === 'yearly') $itemCycle = 'annual';
                    
                    if ($itemCycle !== $compareCycle) {
                        return false;
                    }
                }

                return true;
            })->values();
        }

        // Calculate pure live database numerical statistics to accurately map singular or multiple entries
        $totalCount = $invoices->count();
        $paidCount = $invoices->where('status', 'paid')->count();
        $pendingCount = $invoices->where('status', 'pending')->count();
        $overdueCount = $invoices->where('status', 'overdue')->count();

        return Inertia::render('Invoices/Index', [
            'metrics' => [
                'total_invoices' => number_format($totalCount),
                'paid_invoices' => number_format($paidCount),
                'pending_invoices' => number_format($pendingCount),
                'overdue_invoices' => number_format($overdueCount),
            ],
            'invoices' => $invoices,
            'availableMonths' => $monthsList,
            'availableYears' => $availableYears,
            'availableCycles' => $cyclesList,
            'filters' => [
                'status' => $request->status ?? 'All Status',
                'search' => $request->search ?? '',
                'filter_month' => $selectedMonth,
                'filter_year' => $selectedYear,
                'billing_cycle' => $selectedCycle,
                'page' => $request->page ?? 1,
            ],
        ]);
    }

    /**
     * Dispatch structured printable invoice serial outputs for instant download.
     */
    public function printInvoice(Invoice $invoice)
    {
        $customer = \App\Models\Customer::where(function($q) use ($invoice) {
            $q->where('name', $invoice->customer_name)
              ->orWhere('contact_name', $invoice->customer_name);
        })->first();
        $resolvedAddress = ($customer && $customer->country) ? $customer->country : 'Global Region';

        $output = "========================================================\n";
        $output .= "                 TAX/VAT INVOICE LEDGER                 \n";
        $output .= "========================================================\n\n";
        $output .= "Invoice Ref:      " . $invoice->invoice_number . "\n";
        $output .= "Client Entity:    " . $invoice->customer_name . "\n";
        $output .= "Billing Address:  " . $resolvedAddress . "\n";
        $output .= "Date Issued:      " . ($invoice->invoice_date ?: now()->format('M d, Y')) . "\n";
        $output .= "Target Due:       " . ($invoice->due_date ?: now()->addDays(30)->format('M d, Y')) . "\n";
        $output .= "State Status:     " . strtoupper($invoice->status) . "\n\n";
        $output .= "--------------------------------------------------------\n";
        $output .= "Subtotal Amount:  $" . number_format((float)$invoice->amount, 2) . "\n";
        $output .= "Tax/VAT Support:  $" . number_format((float)$invoice->tax, 2) . "\n";
        $output .= "--------------------------------------------------------\n";
        $output .= "TOTAL SETTLEMENT: $" . number_format((float)$invoice->total, 2) . "\n";
        $output .= "========================================================\n";
        $output .= "Payment instructions: Remit electronic direct wire sum targeting central treasury accounts.\n";

        return response()->streamDownload(function () use ($output) {
            echo $output;
        }, strtolower($invoice->invoice_number) . '_print.txt', [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . strtolower($invoice->invoice_number) . '_print.txt"',
        ]);
    }

    /**
     * Show the provisioning interface for issuing a new customer invoice assignment.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Invoices/Create', [
            'prefill' => [
                'customer_name' => $request->customer_name ?? '',
                'redirect_customer_id' => $request->redirect_customer_id ?? '',
            ]
        ]);
    }

    /**
     * Store newly generated invoice attributes inside persistent storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'status' => 'required|string|in:paid,pending,overdue,draft',
            'invoice_date' => 'nullable|string|max:255',
            'due_date' => 'nullable|string|max:255',
            'billing_cycle' => 'nullable|string|in:monthly,annual,two_months,half_yearly,quarterly,evently',
            'currency' => 'nullable|string|max:10',
            'usd_amount' => 'nullable|numeric|min:0',
            'closed_by' => 'nullable|string|max:255',
        ]);

        // Auto-compute relational sum parameters
        $validated['total'] = $validated['amount'] + $validated['tax'];
        $validated['invoice_number'] = 'INV-' . date('Y') . '-' . strtoupper(substr(uniqid(), -3));

        if (empty($validated['invoice_date'])) {
            $validated['invoice_date'] = now()->format('M d, Y');
        }
        if (empty($validated['due_date'])) {
            $validated['due_date'] = now()->addDays(30)->format('M d, Y');
        }

        Invoice::create($validated);

        if ($request->filled('redirect_customer_id')) {
            return redirect()->route('customers.edit', $request->redirect_customer_id)->with('success', 'Taxation invoice generated successfully.');
        }

        return redirect()->route('invoices.index')->with('success', 'New invoice ledger initialized.');
    }

    /**
     * Show the edit view layout targeted to modify persistent customer invoices.
     */
    public function edit(Request $request, Invoice $invoice): Response
    {
        return Inertia::render('Invoices/Edit', [
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => $invoice->customer_name,
                'amount' => $invoice->amount,
                'tax' => $invoice->tax,
                'status' => $invoice->status,
                'invoice_date' => $invoice->invoice_date,
                'due_date' => $invoice->due_date,
                'billing_cycle' => $invoice->billing_cycle,
                'currency' => $invoice->currency ?? 'USD',
                'usd_amount' => $invoice->usd_amount ?? 0.00,
                'closed_by' => $invoice->closed_by,
            ],
            'returnPage' => (int) $request->input('page', 1),
        ]);
    }

    /**
     * Commit incoming modification properties mapped directly to database target models.
     */
    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'status' => 'required|string|in:paid,pending,overdue,draft',
            'invoice_date' => 'nullable|string|max:255',
            'due_date' => 'nullable|string|max:255',
            'billing_cycle' => 'nullable|string|in:monthly,annual,two_months,half_yearly,quarterly,evently',
            'currency' => 'nullable|string|max:10',
            'usd_amount' => 'nullable|numeric|min:0',
            'closed_by' => 'nullable|string|max:255',
        ]);

        $validated['total'] = $validated['amount'] + $validated['tax'];

        $invoice->update($validated);

        $page = $request->input('page', 1);
        return redirect()->route('invoices.index', ['page' => $page])->with('success', 'Invoice record updated successfully.');
    }

    /**
     * Delete assigned customer invoice targets securely.
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Invoice record removed from accounting registry.');
    }
}
