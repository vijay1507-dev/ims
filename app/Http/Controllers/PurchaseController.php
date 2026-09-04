<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class PurchaseController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Purchase::class, 'purchase');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Purchase::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('purchase_number', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status') && $request->status !== 'All Statuses') {
            $query->where('status', strtolower($request->status));
        }

        $purchases = $query->latest('purchase_date')->get()->map(function ($purchase) {
            return [
                'id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'supplier_name' => $purchase->supplier_name,
                'purchase_date' => $purchase->purchase_date->format('Y-m-d'),
                'total_amount' => (float) $purchase->total_amount,
                'status' => ucfirst($purchase->status),
                'payment_status' => ucfirst(str_replace('_', ' ', $purchase->payment_status)),
            ];
        });

        // Metrics
        $totalSpent = Purchase::where('status', '!=', 'cancelled')->sum('total_amount');
        $receivedCount = Purchase::where('status', 'received')->count();
        $pendingCount = Purchase::where('status', 'pending')->count();
        $orderedCount = Purchase::where('status', 'ordered')->count();

        return Inertia::render('Purchases/Index', [
            'purchases' => $purchases,
            'metrics' => [
                'total_spent' => number_format($totalSpent, 2),
                'received_count' => $receivedCount,
                'pending_count' => $pendingCount,
                'ordered_count' => $orderedCount,
            ],
            'filters' => [
                'search' => $request->search ?? '',
                'status' => $request->status ?? 'All Statuses',
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Purchases/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:255',
            'purchase_date' => 'required|date',
            'status' => 'required|in:pending,ordered,received,cancelled',
            'payment_status' => 'required|in:unpaid,partially_paid,paid',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.category' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $purchase = Purchase::create([
                'supplier_name' => $validated['supplier_name'],
                'purchase_date' => $validated['purchase_date'],
                'status' => $validated['status'],
                'payment_status' => $validated['payment_status'],
                'notes' => $validated['notes'],
                'created_by' => auth()->id(),
            ]);

            $totalAmount = 0;

            foreach ($validated['items'] as $item) {
                $subtotal = $item['quantity'] * $item['unit_cost'];
                $totalAmount += $subtotal;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'item_name' => $item['item_name'],
                    'category' => $item['category'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $subtotal,
                ]);

                // If status is received, auto-create main Inventory models ONLY for inventory-tracked items
                $trackableCategories = ['kiosk', 'tv', 'tablet', 'printer', 'license', 'desktop', 'display', 'mobile'];
                if ($validated['status'] === 'received' && in_array($item['category'], $trackableCategories)) {
                    for ($i = 0; $i < $item['quantity']; $i++) {
                        Inventory::create([
                            'asset_id' => 'AST-' . strtoupper(bin2hex(random_bytes(4))),
                            'category' => $item['category'],
                            'type_name' => 'Hardware Asset',
                            'name_model' => $item['item_name'],
                            'status' => 'active',
                            'assigned_date' => now()->format('Y-m-d'),
                        ]);
                    }
                }
            }

            $purchase->update(['total_amount' => $totalAmount]);
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase order registered successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $purchase): Response
    {
        return Inertia::render('Purchases/Edit', [
            'purchase' => $purchase->load('items'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Purchase $purchase): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:255',
            'purchase_date' => 'required|date',
            'status' => 'required|in:pending,ordered,received,cancelled',
            'payment_status' => 'required|in:unpaid,partially_paid,paid',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.category' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        $oldStatus = $purchase->status;

        DB::transaction(function () use ($validated, $purchase, $oldStatus) {
            $purchase->update([
                'supplier_name' => $validated['supplier_name'],
                'purchase_date' => $validated['purchase_date'],
                'status' => $validated['status'],
                'payment_status' => $validated['payment_status'],
                'notes' => $validated['notes'],
            ]);

            // Re-calculate items
            $purchase->items()->delete();
            $totalAmount = 0;

            foreach ($validated['items'] as $item) {
                $subtotal = $item['quantity'] * $item['unit_cost'];
                $totalAmount += $subtotal;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'item_name' => $item['item_name'],
                    'category' => $item['category'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $subtotal,
                ]);

                // Transition to received triggers inventory generation ONLY for inventory-tracked items
                $trackableCategories = ['kiosk', 'tv', 'tablet', 'printer', 'license', 'desktop', 'display', 'mobile'];
                if ($validated['status'] === 'received' && $oldStatus !== 'received' && in_array($item['category'], $trackableCategories)) {
                    for ($i = 0; $i < $item['quantity']; $i++) {
                        Inventory::create([
                            'asset_id' => 'AST-' . strtoupper(bin2hex(random_bytes(4))),
                            'category' => $item['category'],
                            'type_name' => 'Hardware Asset',
                            'name_model' => $item['item_name'],
                            'status' => 'active',
                            'assigned_date' => now()->format('Y-m-d'),
                        ]);
                    }
                }
            }

            $purchase->update(['total_amount' => $totalAmount]);
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase order updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase): RedirectResponse
    {
        $purchase->delete();

        return redirect()->route('purchases.index')->with('success', 'Purchase order deleted successfully.');
    }
}
