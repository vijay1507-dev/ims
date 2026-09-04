<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Commission;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class CommissionController extends Controller
{
    protected $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * Display the Commissions dashboard listing employees and saved records.
     */
    public function index(): Response
    {
        \Illuminate\Support\Facades\Gate::authorize('payments.view');

        // Fetch unique employee names from the payments closed_by field
        $employees = \App\Models\Payment::whereNotNull('closed_by')
            ->where('closed_by', '!=', '')
            ->distinct()
            ->pluck('closed_by')
            ->sort()
            ->values();
        
        $commissions = Commission::with(['user', 'slab'])
            ->latest()
            ->get();

        $formattedCommissions = $commissions->map(function ($c) {
            $monthName = date('F', mktime(0, 0, 0, $c->month, 10));
            return [
                'id' => $c->id,
                'user_id' => $c->user_id,
                'employee_name' => $c->employee_name,
                'month' => $c->month,
                'year' => $c->year,
                'period' => "{$monthName} {$c->year}",
                'salary' => '₹' . number_format($c->salary, 2),
                'raw_salary' => $c->salary,
                'total_payments_closed' => '₹' . number_format($c->total_payments_closed, 2),
                'raw_total_payments_closed' => $c->total_payments_closed,
                'achievement_percentage' => $c->achievement_percentage . '%',
                'raw_achievement_percentage' => $c->achievement_percentage,
                'points' => $c->points,
                'slab_name' => $c->slab ? '₹' . number_format($c->slab->achievement_amount) : 'No Slab Achieved',
                'commission_percentage' => $c->commission_percentage . '%',
                'raw_commission_percentage' => $c->commission_percentage,
                'commission_amount' => '₹' . number_format($c->commission_amount, 2),
                'raw_commission_amount' => $c->commission_amount,
                'status' => $c->status,
                'calculation_date' => Carbon::parse($c->calculation_date)->format('M d, Y'),
            ];
        });

        // Generate available months for filter list
        $monthsList = [
            ['value' => 1, 'label' => 'January'],
            ['value' => 2, 'label' => 'February'],
            ['value' => 3, 'label' => 'March'],
            ['value' => 4, 'label' => 'April'],
            ['value' => 5, 'label' => 'May'],
            ['value' => 6, 'label' => 'June'],
            ['value' => 7, 'label' => 'July'],
            ['value' => 8, 'label' => 'August'],
            ['value' => 9, 'label' => 'September'],
            ['value' => 10, 'label' => 'October'],
            ['value' => 11, 'label' => 'November'],
            ['value' => 12, 'label' => 'December'],
        ];

        // Generate available years: current year +- 10 years
        $currentYear = (int)date('Y');
        $yearsList = [];
        for ($y = $currentYear - 5; $y <= $currentYear + 5; $y++) {
            $yearsList[] = $y;
        }

        return Inertia::render('Commissions/Index', [
            'employees' => $employees,
            'commissions' => $formattedCommissions,
            'availableMonths' => $monthsList,
            'availableYears' => $yearsList,
        ]);
    }

    /**
     * Run the commission calculation dynamically and return JSON payload.
     */
    public function calculate(Request $request): JsonResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('payments.view');

        $validated = $request->validate([
            'employee_name' => 'required|string|max:255',
            'salary' => 'required|numeric|min:0.01',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer',
        ]);

        try {
            $result = $this->commissionService->calculate(
                $validated['employee_name'],
                (float)$validated['salary'],
                (int)$validated['month'],
                (int)$validated['year']
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Store/Save a commission record to database storage.
     */
    public function store(Request $request): RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('payments.view');

        $validated = $request->validate([
            'employee_name' => 'required|string|max:255',
            'salary' => 'required|numeric|min:0.01',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer',
            'status' => 'required|string|in:draft,calculated,approved,paid',
        ]);

        $this->commissionService->save(
            $validated['employee_name'],
            (float)$validated['salary'],
            (int)$validated['month'],
            (int)$validated['year'],
            $validated['status']
        );

        return redirect()->route('commissions.index')->with('success', 'Commission record saved successfully.');
    }

    /**
     * Update the status of the specified commission record.
     */
    public function update(Request $request, Commission $commission): RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('payments.view');

        $validated = $request->validate([
            'status' => 'required|string|in:draft,calculated,approved,paid',
        ]);

        $commission->update($validated);

        return redirect()->back()->with('success', 'Commission status updated successfully.');
    }

    /**
     * Delete the specified commission record from database storage.
     */
    public function destroy(Commission $commission): RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('payments.view');

        $commission->delete();

        return redirect()->back()->with('success', 'Commission record deleted successfully.');
    }
}
