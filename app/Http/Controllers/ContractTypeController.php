<?php

namespace App\Http\Controllers;

use App\Models\ContractType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ContractTypeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ContractType::class);
    }

    /**
     * Display a listing of contract types.
     */
    public function index(Request $request): Response
    {
        $query = ContractType::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status') && $request->status !== 'All Status') {
            $query->where('status', strtolower($request->status));
        }

        $contractTypes = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('ContractTypes/Index', [
            'contractTypes' => $contractTypes,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Store a newly created contract type.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:contract_types,name',
            'description' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
        ]);

        $validated['created_by'] = Auth::id();

        ContractType::create($validated);

        return redirect()->back()->with('success', 'Contract Type created successfully.');
    }

    /**
     * Update the specified contract type.
     */
    public function update(Request $request, ContractType $contractType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:contract_types,name,' . $contractType->id,
            'description' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
        ]);

        $validated['updated_by'] = Auth::id();

        $contractType->update($validated);

        return redirect()->back()->with('success', 'Contract Type updated successfully.');
    }

    /**
     * Remove the specified contract type.
     */
    public function destroy(ContractType $contractType): RedirectResponse
    {
        // Do not allow deletion of a Contract Type if it is already being used by existing Contracts.
        if ($contractType->contracts()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete this Contract Type as it is in use by existing contracts. Please deactivate it instead.');
        }

        $contractType->delete();

        return redirect()->back()->with('success', 'Contract Type deleted successfully.');
    }

    /**
     * Toggle status active/inactive
     */
    public function toggleStatus(ContractType $contractType): RedirectResponse
    {
        $newStatus = $contractType->status === 'active' ? 'inactive' : 'active';
        $contractType->update([
            'status' => $newStatus,
            'updated_by' => Auth::id()
        ]);

        return redirect()->back()->with('success', 'Contract Type status updated successfully.');
    }
}
