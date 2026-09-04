<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Expense::class, 'expense');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Expense::query();

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->filled('type') && $request->type !== 'All Types') {
            $query->where('type', strtolower($request->type));
        }

        // Filter by month
        if ($request->filled('month') && $request->month !== 'All Months') {
            $query->whereMonth('expense_date', $request->month);
        }

        // Filter by year
        if ($request->filled('year') && $request->year !== 'All Years') {
            $query->whereYear('expense_date', $request->year);
        }

        $expenses = $query->latest('expense_date')->get()->map(function ($expense) {
            return [
                'id' => $expense->id,
                'title' => $expense->title,
                'type' => ucfirst($expense->type),
                'category' => $expense->category,
                'amount' => (float) $expense->amount,
                'expense_date' => $expense->expense_date->format('Y-m-d'),
                'reference_number' => $expense->reference_number,
                'description' => $expense->description,
            ];
        });

        // Compute metrics
        $totalExpenses = Expense::sum('amount');
        $directExpenses = Expense::where('type', 'direct')->sum('amount');
        $indirectExpenses = Expense::where('type', 'indirect')->sum('amount');

        // Generate list containing previous 10 years and next 10 years of the current year
        $currentYear = (int)date('Y');
        $years = [];
        for ($y = $currentYear - 10; $y <= $currentYear + 10; $y++) {
            $years[] = (string)$y;
        }
        rsort($years);

        return Inertia::render('Expenses/Index', [
            'expenses' => $expenses,
            'metrics' => [
                'total' => number_format($totalExpenses, 2),
                'direct' => number_format($directExpenses, 2),
                'indirect' => number_format($indirectExpenses, 2),
                'direct_percentage' => $totalExpenses > 0 ? round(($directExpenses / $totalExpenses) * 100, 1) : 0,
                'indirect_percentage' => $totalExpenses > 0 ? round(($indirectExpenses / $totalExpenses) * 100, 1) : 0,
            ],
            'filters' => [
                'search' => $request->search ?? '',
                'type' => $request->type ?? 'All Types',
                'month' => $request->month ?? 'All Months',
                'year' => $request->year ?? 'All Years',
            ],
            'available_years' => $years,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Expenses/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:direct,indirect',
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
            'reference_number' => 'nullable|string|max:255',
        ]);

        $validated['created_by'] = auth()->id();

        Expense::create($validated);

        return redirect()->route('expenses.index')->with('success', 'Expense logged successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense): Response
    {
        return Inertia::render('Expenses/Edit', [
            'expense' => [
                'id' => $expense->id,
                'title' => $expense->title,
                'type' => $expense->type,
                'category' => $expense->category,
                'amount' => $expense->amount,
                'expense_date' => $expense->expense_date->format('Y-m-d'),
                'reference_number' => $expense->reference_number,
                'description' => $expense->description,
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:direct,indirect',
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
            'reference_number' => 'nullable|string|max:255',
        ]);

        $expense->update($validated);

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
    }

    /**
     * Download expense receipt voucher.
     */
    public function downloadReceipt(Expense $expense)
    {
        $expense->load('creator');
        $html = view('expenses.receipt', compact('expense'))->render();
        $fileName = 'expense_' . $expense->id . '_receipt.html';

        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
