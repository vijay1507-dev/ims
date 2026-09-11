<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Contract::class);
    }

    /**
     * Display a listing of contracts.
     */
    public function index(Request $request): Response
    {
        $query = Contract::with(['contractType', 'customer']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('contract_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhereHas('contractType', function ($ct) use ($search) {
                      $ct->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filters
        if ($request->filled('status') && $request->status !== 'All Status') {
            $query->where('status', strtolower($request->status));
        }

        if ($request->filled('contract_type_id') && $request->contract_type_id !== 'All Types') {
            $query->where('contract_type_id', $request->contract_type_id);
        }


        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        $contracts = $query->latest()->paginate(10)->withQueryString();

        // Get dropdown resources
        $allContractTypes = ContractType::orderBy('name')->get();
        $activeContractTypes = $allContractTypes->where('status', 'active')->values();

        return Inertia::render('Contracts/Index', [
            'contracts' => $contracts,
            'activeContractTypes' => $activeContractTypes,
            'allContractTypes' => $allContractTypes,
            'filters' => $request->only(['search', 'status', 'contract_type_id', 'start_date', 'end_date']),
        ]);
    }

    /**
     * Store a newly created contract.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|string|in:pending,active,expired,terminated',
            'contract_type_id' => 'required|exists:contract_types,id',
            'customer_name' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        // Only active Contract Types can be selected for new Contracts.
        $type = ContractType::findOrFail($request->contract_type_id);
        if ($type->status !== 'active') {
            return back()->withErrors(['contract_type_id' => 'Only active Contract Types can be selected.']);
        }

        $validated['created_by'] = Auth::id();

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $validated['attachment_path'] = $file->store('contracts', 'public');
            $validated['attachment_name'] = $file->getClientOriginalName();
        }
        unset($validated['attachment']);

        Contract::create($validated);

        return redirect()->back()->with('success', 'Contract created successfully.');
    }

    /**
     * Update the specified contract.
     */
    public function update(Request $request, Contract $contract): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|string|in:pending,active,expired,terminated',
            'contract_type_id' => 'required|exists:contract_types,id',
            'customer_name' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        // Only active Contract Types can be selected for new/edit Contracts, unless it's already using it.
        if ($contract->contract_type_id != $request->contract_type_id) {
            $type = ContractType::findOrFail($request->contract_type_id);
            if ($type->status !== 'active') {
                return back()->withErrors(['contract_type_id' => 'Only active Contract Types can be selected.']);
            }
        }

        $validated['updated_by'] = Auth::id();

        if ($request->hasFile('attachment')) {
            if ($contract->attachment_path) {
                Storage::disk('public')->delete($contract->attachment_path);
            }
            $file = $request->file('attachment');
            $validated['attachment_path'] = $file->store('contracts', 'public');
            $validated['attachment_name'] = $file->getClientOriginalName();
        }
        unset($validated['attachment']);

        $contract->update($validated);

        return redirect()->back()->with('success', 'Contract updated successfully.');
    }

    /**
     * Remove the specified contract.
     */
    public function destroy(Contract $contract): RedirectResponse
    {
        $contract->delete();

        return redirect()->back()->with('success', 'Contract deleted successfully.');
    }

    /**
     * Duplicate the specified contract.
     */
    public function duplicate(Contract $contract): RedirectResponse
    {
        $newContract = $contract->replicate();
        $newContract->contract_number = null; // will be auto-generated in static creating boot
        $newContract->created_by = Auth::id();
        $newContract->updated_by = null;
        $newContract->save();

        return redirect()->back()->with('success', 'Contract duplicated successfully.');
    }

    /**
     * Stream the contract's attachment (avoids relying on the public/storage symlink,
     * which many shared hosts don't support).
     */
    public function attachment(Contract $contract)
    {
        $this->authorize('view', $contract);

        if (! $contract->attachment_path || ! Storage::disk('public')->exists($contract->attachment_path)) {
            abort(404);
        }

        return Storage::disk('public')->response(
            $contract->attachment_path,
            $contract->attachment_name
        );
    }
}
