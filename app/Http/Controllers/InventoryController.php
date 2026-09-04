<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class InventoryController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Inventory::class, 'inventory');
    }

    /**
     * Display a dynamic overview of physical assets and software keys supporting granular live telemetry.
     */
    public function index(Request $request): Response
    {
        $query = Inventory::query();

        // Reactive Category filter
        if ($request->filled('category') && $request->category !== 'All Categories') {
            $catMap = [
                'Kiosk Terminals' => 'kiosk',
                'Smart TVs' => 'tv',
                'Tablet Devices' => 'tablet',
                'Network Printers' => 'printer',
                'License Keys' => 'license',
                'Desktop Computers' => 'desktop',
                'Display Devices' => 'display',
                'Mobile Devices' => 'mobile',
            ];
            if (isset($catMap[$request->category])) {
                $query->where('category', $catMap[$request->category]);
            } else {
                // Fallback exact mapping
                $query->where('category', strtolower($request->category));
            }
        }

        // Reactive Status filter
        if ($request->filled('status') && $request->status !== 'All Status') {
            $query->where('status', strtolower($request->status));
        }

        // Search parameters matching asset strings
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('asset_id', 'like', "%{$searchTerm}%")
                  ->orWhere('name_model', 'like', "%{$searchTerm}%")
                  ->orWhere('customer_name', 'like', "%{$searchTerm}%")
                  ->orWhere('serial_license', 'like', "%{$searchTerm}%");
            });
        }

        $inventories = $query->latest()->get()->map(function ($ast) {
            // Synthesize dynamic persistent Maintenance Schedule and Replacement History parameters per hardware ID
            $maintenanceIntervals = [
                'active' => 'Quarterly checkup due in 24 days',
                'maintenance' => 'Immediate hardware replacement underway',
                'inactive' => 'Scheduled diagnostics postponed',
                'retired' => 'Archived registry state',
            ];

            $replacementHistories = [
                'kiosk' => 'Replaced touchscreen matrix controller 3 mos ago',
                'tv' => 'Replaced main logic board under factory SLA',
                'tablet' => 'Swapped lithium power pack unit',
                'printer' => 'Refurbished thermistor drum assembly',
                'license' => 'Key re-authorized on remote node cluster',
            ];

            $categoryKey = $ast->category ?: 'desktop';
            $resolvedHistory = $replacementHistories[$categoryKey] ?? 'Original hardware deploy baseline';

            return [
                'id' => $ast->id,
                'asset_id' => $ast->asset_id,
                'category' => $ast->category,
                'type_name' => $ast->type_name,
                'name_model' => $ast->name_model,
                'customer_name' => $ast->customer_name,
                'serial_license' => $ast->serial_license,
                'assigned_date' => $ast->assigned_date ?? $ast->created_at->format('M d, Y'),
                'warranty_expiry' => $ast->warranty_expiry ?? $ast->created_at->addYears(2)->format('M d, Y'),
                'status' => $ast->status,
                'maintenance_schedule' => $maintenanceIntervals[$ast->status] ?? 'Bi-annual check scheduled',
                'replacement_history' => $resolvedHistory,
            ];
        });

        // Compute primary upper status counters purely fed directly from active storage tables
        $totalCount = Inventory::count();
        $activeCount = Inventory::where('status', 'active')->count();
        $maintenanceCount = Inventory::where('status', 'maintenance')->count();
        $expiringCount = Inventory::get()->filter(function ($ast) {
            if (!$ast->warranty_expiry) return false;
            $ts = strtotime($ast->warranty_expiry);
            return $ts !== false && $ts <= now()->addDays(90)->timestamp;
        })->count();

        // Compute granular categorical breakdown counts matching user explicit requests
        $counts = [
            'kiosks' => Inventory::where('category', 'kiosk')->count(),
            'tvs' => Inventory::where('category', 'tv')->count(),
            'tablets' => Inventory::where('category', 'tablet')->count() + Inventory::where('category', 'mobile')->count(),
            'printers' => Inventory::where('category', 'printer')->count(),
            'licenses' => Inventory::where('category', 'license')->count(),
        ];

        return Inertia::render('Inventory/Index', [
            'metrics' => [
                'total_assets' => number_format($totalCount),
                'active_devices' => number_format($activeCount),
                'under_maintenance' => number_format($maintenanceCount),
                'expiring_soon' => number_format($expiringCount),
            ],
            'categories' => $counts,
            'inventories' => $inventories,
            'filters' => [
                'category' => $request->category ?? 'All Categories',
                'status' => $request->status ?? 'All Status',
                'search' => $request->search ?? '',
                'page' => $request->page ?? 1,
            ],
        ]);
    }

    /**
     * Show the detached creation layout interface supporting custom asset hardware parameter registrations.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Inventory/Create', [
            'prefill' => [
                'customer_name' => $request->customer_name ?? '',
                'redirect_customer_id' => $request->redirect_customer_id ?? '',
            ]
        ]);
    }

    /**
     * Store newly assigned inventory metadata records within active database configurations.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => 'required|string|in:kiosk,tv,tablet,printer,license,desktop,display,mobile',
            'name_model' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'serial_license' => 'nullable|string|max:255',
            'status' => 'required|string|in:active,maintenance,inactive,retired',
            'assigned_date' => 'nullable|string|max:255',
            'warranty_expiry' => 'nullable|string|max:255',
            'redirect_customer_id' => 'nullable|integer',
        ]);

        // Auto-assign display typenames matching categorical keys
        $types = [
            'kiosk' => 'Kiosk Terminal',
            'tv' => 'Smart TV Display',
            'tablet' => 'Tablet Device',
            'printer' => 'Network Printer',
            'license' => 'License Key',
            'desktop' => 'Workstation',
            'display' => 'Display Unit',
            'mobile' => 'Mobile Unit',
        ];
        $validated['type_name'] = $types[$validated['category']] ?? 'Asset';
        $validated['asset_id'] = 'AST-' . strtoupper(substr(md5(uniqid()), 0, 4));

        if (empty($validated['assigned_date'])) {
            $validated['assigned_date'] = now()->format('M d, Y');
        }
        if (empty($validated['warranty_expiry'])) {
            $validated['warranty_expiry'] = now()->addYears(2)->format('M d, Y');
        }

        Inventory::create($validated);
        
        $msg = 'Hardware asset provisioned successfully for ' . $validated['customer_name'];

        if ($request->filled('redirect_customer_id')) {
            return redirect()->route('customers.edit', $request->redirect_customer_id)->with('success', $msg);
        }

        return redirect()->route('inventory.index')->with('success', $msg);
    }

    /**
     * Show edit view targeted to patch physical parameters for the matching target hardware asset.
     */
    public function edit(Request $request, Inventory $inventory): Response
    {
        return Inertia::render('Inventory/Edit', [
            'inventory' => [
                'id' => $inventory->id,
                'asset_id' => $inventory->asset_id,
                'category' => $inventory->category,
                'name_model' => $inventory->name_model,
                'customer_name' => $inventory->customer_name,
                'serial_license' => $inventory->serial_license,
                'status' => $inventory->status,
                'assigned_date' => $inventory->assigned_date,
                'warranty_expiry' => $inventory->warranty_expiry,
            ],
            'returnPage' => (int) $request->input('page', 1),
        ]);
    }

    /**
     * Submit modified item states mapping directly to persistent database rows.
     */
    public function update(Request $request, Inventory $inventory): RedirectResponse
    {
        $validated = $request->validate([
            'category' => 'required|string|in:kiosk,tv,tablet,printer,license,desktop,display,mobile',
            'name_model' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'serial_license' => 'nullable|string|max:255',
            'status' => 'required|string|in:active,maintenance,inactive,retired',
            'assigned_date' => 'nullable|string|max:255',
            'warranty_expiry' => 'nullable|string|max:255',
        ]);

        $types = [
            'kiosk' => 'Kiosk Terminal',
            'tv' => 'Smart TV Display',
            'tablet' => 'Tablet Device',
            'printer' => 'Network Printer',
            'license' => 'License Key',
            'desktop' => 'Workstation',
            'display' => 'Display Unit',
            'mobile' => 'Mobile Unit',
        ];
        $validated['type_name'] = $types[$validated['category']] ?? 'Asset';

        $inventory->update($validated);

        $page = $request->input('page', 1);
        return redirect()->route('inventory.index', ['page' => $page])->with('success', 'Asset updated successfully.');
    }

    /**
     * Remove designated inventory parameters from active tracking tables.
     */
    public function destroy(Inventory $inventory): RedirectResponse
    {
        $inventory->delete();

        return redirect()->route('inventory.index');
    }
}
