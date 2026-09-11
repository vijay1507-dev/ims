<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\BillingCycle;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class BillingCycleController extends Controller
{
    public function index(Request $request): Response
    {
        \Illuminate\Support\Facades\Gate::authorize('settings.manage');

        $billingCycles = BillingCycle::orderBy('name')->get();

        return Inertia::render('BillingCycles/Index', [
            'billingCycles' => $billingCycles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('settings.manage');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|lowercase|alpha_dash|unique:billing_cycles,code|max:255',
            'duration_months' => 'required|integer|min:1',
            'status' => 'required|string|in:active,inactive',
        ]);

        BillingCycle::create($validated);

        return redirect()->back()->with('success', 'Billing cycle created successfully.');
    }

    public function update(Request $request, BillingCycle $billingCycle): RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('settings.manage');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|lowercase|alpha_dash|max:255|unique:billing_cycles,code,' . $billingCycle->id,
            'duration_months' => 'required|integer|min:1',
            'status' => 'required|string|in:active,inactive',
        ]);

        $billingCycle->update($validated);

        return redirect()->back()->with('success', 'Billing cycle updated successfully.');
    }

    public function destroy(BillingCycle $billingCycle): RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('settings.manage');

        // Check if there are active subscriptions using this cycle
        $usageCount = \DB::table('subscriptions')->where('billing_cycle', $billingCycle->code)->count();
        if ($usageCount > 0) {
            return redirect()->back()->with('error', 'Cannot delete this billing cycle. It is currently assigned to ' . $usageCount . ' subscriptions.');
        }

        $billingCycle->delete();

        return redirect()->back()->with('success', 'Billing cycle deleted successfully.');
    }

    public function toggleStatus(BillingCycle $billingCycle): RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('settings.manage');

        $newStatus = $billingCycle->status === 'active' ? 'inactive' : 'active';
        $billingCycle->update(['status' => $newStatus]);

        return redirect()->route('billing-cycles.index')->with('success', 'Billing cycle status updated successfully.');
    }
}
