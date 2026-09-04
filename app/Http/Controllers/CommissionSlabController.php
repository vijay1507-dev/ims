<?php

namespace App\Http\Controllers;

use App\Models\CommissionSlab;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class CommissionSlabController extends Controller
{
    /**
     * Display a listing of configured commission slabs.
     */
    public function index(): Response
    {
        \Illuminate\Support\Facades\Gate::authorize('settings.manage');

        return Inertia::render('CommissionSlabs/Index', [
            'slabs' => CommissionSlab::orderBy('achievement_amount', 'asc')->get(),
        ]);
    }

    /**
     * Store a newly created commission slab.
     */
    public function store(Request $request): RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('settings.manage');

        $validated = $request->validate([
            'achievement_amount' => 'required|numeric|min:0',
            'achievement_percentage' => 'required|numeric|min:0',
            'points' => 'required|integer|min:0',
            'commission_percentage' => 'required|numeric|min:0',
            'status' => 'required|string|in:active,inactive',
        ]);

        CommissionSlab::create($validated);

        return redirect()->back()->with('success', 'Commission slab created successfully.');
    }

    /**
     * Update the specified commission slab.
     */
    public function update(Request $request, CommissionSlab $commissionSlab): RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('settings.manage');

        $validated = $request->validate([
            'achievement_amount' => 'required|numeric|min:0',
            'achievement_percentage' => 'required|numeric|min:0',
            'points' => 'required|integer|min:0',
            'commission_percentage' => 'required|numeric|min:0',
            'status' => 'required|string|in:active,inactive',
        ]);

        $commissionSlab->update($validated);

        return redirect()->back()->with('success', 'Commission slab updated successfully.');
    }

    /**
     * Toggle the active status of the specified commission slab.
     */
    public function toggleStatus(CommissionSlab $commissionSlab): RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('settings.manage');

        $newStatus = $commissionSlab->status === 'active' ? 'inactive' : 'active';
        $commissionSlab->update(['status' => $newStatus]);

        return redirect()->back()->with('success', 'Slab status updated successfully.');
    }

    /**
     * Remove the specified commission slab.
     */
    public function destroy(CommissionSlab $commissionSlab): RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('settings.manage');

        $commissionSlab->delete();

        return redirect()->back()->with('success', 'Commission slab deleted successfully.');
    }
}
