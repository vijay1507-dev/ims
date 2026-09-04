<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Handle the application entry point overview dashboard.
     */
    public function index(DashboardService $dashboardService)
    {
        \App\Models\Subscription::syncAllCustomersWithSubscriptions();
        $user = auth()->user();

        if (!$user->can('dashboard.view')) {
            $navigation = [
                'customers.view' => 'customers.index',
                'subscriptions.view' => 'subscriptions.index',
                'payments.view' => 'payments.index',
                'invoices.view' => 'invoices.index',
                'inventory.view' => 'inventory.index',
                'renewals.view' => 'renewals.index',
                'reports.view' => 'reports.index',
                'users.manage' => 'users.index',
                'settings.manage' => 'settings.index',
            ];

            foreach ($navigation as $permission => $route) {
                if ($user->can($permission)) {
                    return redirect()->route($route);
                }
            }

            // Fallback if no permissions at all
            abort(403, 'You do not have permission to access any modules.');
        }

        return Inertia::render('Dashboard/Index', [
            'kpis' => $dashboardService->getKpiMetrics(),
            'charts' => $dashboardService->getChartDatasets(),
            'activities' => $dashboardService->getRecentActivities(),
            'quickStats' => $dashboardService->getQuickStats(),
        ]);
    }

    public function activities(DashboardService $dashboardService)
    {
        $user = auth()->user();

        if (!$user->can('dashboard.view')) {
            abort(403, 'You do not have permission to access activities.');
        }

        return Inertia::render('Dashboard/Activities', [
            'activities' => $dashboardService->getAllActivities(),
        ]);
    }

    public function globalSearch(\Illuminate\Http\Request $request)
    {
        $q = $request->query('q');
        if (empty($q) || strlen($q) < 2) {
            return response()->json([
                'customers' => [],
                'subscriptions' => [],
                'payments' => [],
                'invoices' => [],
            ]);
        }

        $customers = \App\Models\Customer::where('name', 'like', "%{$q}%")
            ->orWhere('contact_name', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->take(5)
            ->get(['id', 'name', 'contact_name', 'email']);

        $subscriptions = \App\Models\Subscription::where('customer_name', 'like', "%{$q}%")
            ->orWhere('plan_name', 'like', "%{$q}%")
            ->take(5)
            ->get(['id', 'customer_name', 'plan_name', 'amount']);

        $payments = \App\Models\Payment::where('customer_name', 'like', "%{$q}%")
            ->orWhere('transaction_id', 'like', "%{$q}%")
            ->take(5)
            ->get(['id', 'customer_name', 'transaction_id', 'amount']);

        $invoices = \App\Models\Invoice::where('invoice_number', 'like', "%{$q}%")
            ->orWhere('customer_name', 'like', "%{$q}%")
            ->take(5)
            ->get(['id', 'invoice_number', 'customer_name', 'total']);

        return response()->json([
            'customers' => $customers,
            'subscriptions' => $subscriptions,
            'payments' => $payments,
            'invoices' => $invoices,
        ]);
    }
}
