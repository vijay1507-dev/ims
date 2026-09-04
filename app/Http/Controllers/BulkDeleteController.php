<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Inventory;
use App\Models\ActivityLog;

class BulkDeleteController extends Controller
{
    private function getModuleConfig($module)
    {
        $config = [
            'customers' => [
                'model' => Customer::class,
                'date_col' => 'joined_date',
                'perm' => 'bulk-delete-customers',
                'name' => 'Customers',
            ],
            'subscriptions' => [
                'model' => Subscription::class,
                'date_col' => 'start_date',
                'perm' => 'bulk-delete-subscriptions',
                'name' => 'Subscriptions',
            ],
            'payments' => [
                'model' => Payment::class,
                'date_col' => 'payment_date',
                'perm' => 'bulk-delete-payments',
                'name' => 'Payments',
            ],
            'invoices' => [
                'model' => Invoice::class,
                'date_col' => 'invoice_date',
                'perm' => 'bulk-delete-invoices',
                'name' => 'Invoices',
            ],
            'inventory' => [
                'model' => Inventory::class,
                'date_col' => 'assigned_date',
                'perm' => 'bulk-delete-inventory',
                'name' => 'Inventory',
            ],
        ];

        return $config[strtolower($module)] ?? null;
    }

    private function getPeriodRange($type, $period)
    {
        if ($type === 'month') {
            // period format should be YYYY-MM (e.g. 2026-08)
            $start = $period . '-01';
            $end = date('Y-m-d', strtotime($start . ' +1 month'));
        } else {
            // period format should be YYYY (e.g. 2026)
            $start = $period . '-01-01';
            $end = ((int)$period + 1) . '-01-01';
        }
        return [$start, $end];
    }

    private function getMatchingRecords($cfg, $type, $period)
    {
        list($startStr, $endStr) = $this->getPeriodRange($type, $period);
        $startTimestamp = strtotime($startStr);
        $endTimestamp = strtotime($endStr);

        $allRecords = $cfg['model']::all();
        $matching = [];

        foreach ($allRecords as $record) {
            $dateVal = $record->{$cfg['date_col']};
            if (empty($dateVal)) {
                $ts = $record->created_at ? $record->created_at->timestamp : false;
            } else {
                $ts = strtotime($dateVal);
                if ($ts === false) {
                    if (preg_match('/^(\d{1,2})[-|\/](\d{1,2})[-|\/](\d{4})$/', trim($dateVal), $matches)) {
                        $ts = mktime(0, 0, 0, (int)$matches[2], (int)$matches[1], (int)$matches[3]);
                    }
                }
            }

            if ($ts !== false && $ts >= $startTimestamp && $ts < $endTimestamp) {
                $matching[] = $record;
            }
        }

        return collect($matching);
    }

    public function count(Request $request)
    {
        $request->validate([
            'module' => 'required|string',
            'type' => 'required|string|in:month,year',
            'period' => 'required|string',
        ]);

        $module = $request->input('module');
        $cfg = $this->getModuleConfig($module);
        if (!$cfg) {
            return response()->json(['error' => 'Invalid module.'], 400);
        }

        Gate::authorize($cfg['perm']);

        $records = $this->getMatchingRecords($cfg, $request->input('type'), $request->input('period'));
        $count = $records->count();

        return response()->json(['count' => $count]);
    }

    public function execute(Request $request)
    {
        $request->validate([
            'module' => 'required|string',
            'type' => 'required|string|in:month,year',
            'period' => 'required|string',
        ]);

        $module = $request->input('module');
        $cfg = $this->getModuleConfig($module);
        if (!$cfg) {
            return response()->json(['error' => 'Invalid module.'], 400);
        }

        Gate::authorize($cfg['perm']);

        $records = $this->getMatchingRecords($cfg, $request->input('type'), $request->input('period'));

        if ($records->isEmpty()) {
            return response()->json(['success' => true, 'count' => 0, 'cascaded' => []]);
        }

        $count = $records->count();
        $cascadeCounts = [
            'customers' => 0,
            'subscriptions' => 0,
            'payments' => 0,
            'invoices' => 0,
            'inventories' => 0,
        ];

        list($start, $end) = $this->getPeriodRange($request->input('type'), $request->input('period'));

        DB::beginTransaction();
        try {
            foreach ($records as $record) {
                // Determine cascades based on module type
                if ($module === 'customers') {
                    // Soft delete subscriptions
                    $subs = Subscription::where('customer_email', $record->email)
                        ->orWhere('customer_name', $record->name)
                        ->orWhere('customer_name', $record->contact_name)
                        ->get();
                    foreach ($subs as $sub) {
                        $sub->delete();
                        $cascadeCounts['subscriptions']++;
                    }

                    // Soft delete invoices
                    $invs = Invoice::where('customer_name', $record->name)
                        ->orWhere('customer_name', $record->contact_name)
                        ->get();
                    foreach ($invs as $inv) {
                        $inv->delete();
                        $cascadeCounts['invoices']++;
                    }

                    // Soft delete payments
                    $pays = Payment::where('customer_name', $record->name)
                        ->orWhere('customer_name', $record->contact_name)
                        ->get();
                    foreach ($pays as $pay) {
                        $pay->delete();
                        $cascadeCounts['payments']++;
                    }

                    // Soft delete inventory
                    $devs = Inventory::where('customer_name', $record->name)
                        ->orWhere('customer_name', $record->contact_name)
                        ->get();
                    foreach ($devs as $dev) {
                        $dev->delete();
                        $cascadeCounts['inventories']++;
                    }
                } elseif ($module === 'subscriptions') {
                    // Soft delete payments associated with subscription's customer name
                    $pays = Payment::where('customer_name', $record->customer_name)->get();
                    foreach ($pays as $pay) {
                        // Soft delete invoices matching this payment's invoice_ref
                        if (!empty($pay->invoice_ref)) {
                            $invs = Invoice::where('invoice_number', $pay->invoice_ref)->get();
                            foreach ($invs as $inv) {
                                $inv->delete();
                                $cascadeCounts['invoices']++;
                            }
                        }
                        $pay->delete();
                        $cascadeCounts['payments']++;
                    }
                } elseif ($module === 'invoices') {
                    // Soft delete payments where invoice_ref equals invoice_number
                    $pays = Payment::where('invoice_ref', $record->invoice_number)->get();
                    foreach ($pays as $pay) {
                        $pay->delete();
                        $cascadeCounts['payments']++;
                    }
                } elseif ($module === 'payments') {
                    // Soft delete invoices where invoice_number equals payment's invoice_ref
                    if (!empty($record->invoice_ref)) {
                        $invs = Invoice::where('invoice_number', $record->invoice_ref)->get();
                        foreach ($invs as $inv) {
                            $inv->delete();
                            $cascadeCounts['invoices']++;
                        }
                    }
                }

                // Delete the parent record itself
                $record->delete();
            }

            // Construct activity log
            $userName = Auth::user() ? Auth::user()->name : 'System';
            $periodLabel = $request->input('type') === 'month' ? date('F Y', strtotime($start)) : $request->input('period');
            
            $cascadeStrings = [];
            foreach ($cascadeCounts as $table => $cCount) {
                if ($cCount > 0) {
                    $cascadeStrings[] = "{$table}: {$cCount}";
                }
            }
            $cascadeDetails = empty($cascadeStrings) ? 'None' : implode(', ', $cascadeStrings);

            $detailsLog = "Action: Bulk Soft Delete\n"
                        . "Module: {$cfg['name']}\n"
                        . "Period: {$periodLabel}\n"
                        . "Type: " . ucfirst($request->input('type')) . "\n"
                        . "Records: {$count}\n"
                        . "Related Records Soft Deleted: {$cascadeDetails}\n"
                        . "Deleted By: {$userName}";

            ActivityLog::create([
                'user_name' => $userName,
                'action' => 'Bulk Delete',
                'model_type' => $cfg['name'],
                'reference_id' => $periodLabel,
                'message' => "{$userName} performed Bulk Soft Delete on {$cfg['name']} for {$periodLabel}",
                'details' => $detailsLog,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Deletion failed: ' . $e->getMessage()], 500);
        }

        return redirect()->back()->with('success', "Successfully soft deleted {$count} records and associated cascade traces.");
    }
}
