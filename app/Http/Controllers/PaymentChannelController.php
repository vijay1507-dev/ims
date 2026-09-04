<?php

namespace App\Http\Controllers;

use App\Models\PaymentChannel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaymentChannelController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(PaymentChannel::class);
    }

    /**
     * Display a listing of payment channels.
     */
    public function index(Request $request): Response
    {
        $query = PaymentChannel::query();

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status') && $request->status !== 'All Status') {
            $query->where('status', strtolower($request->status));
        }

        $paymentChannels = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('PaymentChannels/Index', [
            'paymentChannels' => $paymentChannels,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Store a newly created payment channel.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('payment_channels', 'name')->whereNull('deleted_at')
            ],
            'code' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('payment_channels', 'code')->whereNull('deleted_at')
            ],
            'description' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
        ], [
            'code.unique' => 'A payment channel with a matching name or code already exists.',
        ]);

        // Check if there is a soft-deleted channel with matching name or code
        $existing = PaymentChannel::onlyTrashed()
            ->where(function($q) use ($request) {
                $q->where('name', $request->name)
                  ->orWhere('code', $request->code);
            })
            ->first();

        if ($existing) {
            $existing->restore();
            $existing->update([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'status' => $request->status,
                'updated_by' => Auth::id(),
            ]);

            return redirect()->route('payment-channels.index')->with('success', 'Payment Channel created successfully.');
        }

        $validated['created_by'] = Auth::id();

        PaymentChannel::create($validated);

        return redirect()->route('payment-channels.index')->with('success', 'Payment Channel created successfully.');
    }

    /**
     * Update the specified payment channel.
     */
    public function update(Request $request, PaymentChannel $paymentChannel): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('payment_channels', 'name')
                    ->ignore($paymentChannel->id)
                    ->whereNull('deleted_at')
            ],
            'code' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('payment_channels', 'code')
                    ->ignore($paymentChannel->id)
                    ->whereNull('deleted_at')
            ],
            'description' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
        ], [
            'code.unique' => 'A payment channel with a matching name or code already exists.',
        ]);

        $validated['updated_by'] = Auth::id();

        $paymentChannel->update($validated);

        $page = $request->input('page', 1);

        return redirect()->route('payment-channels.index', ['page' => $page])->with('success', 'Payment Channel updated successfully.');
    }

    /**
     * Remove the specified payment channel.
     */
    public function destroy(PaymentChannel $paymentChannel): RedirectResponse
    {
        // Do not allow deletion of a Payment Channel if it is already being used by existing Payments.
        if ($paymentChannel->payments()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete this Payment Channel as it is in use by existing payments. Please deactivate it instead.');
        }

        $paymentChannel->delete();

        return redirect()->route('payment-channels.index')->with('success', 'Payment Channel deleted successfully.');
    }

    /**
     * Toggle status active/inactive
     */
    public function toggleStatus(PaymentChannel $paymentChannel): RedirectResponse
    {
        $newStatus = $paymentChannel->status === 'active' ? 'inactive' : 'active';
        $paymentChannel->update([
            'status' => $newStatus,
            'updated_by' => Auth::id()
        ]);

        return redirect()->back()->with('success', 'Payment Channel status updated successfully.');
    }
}
