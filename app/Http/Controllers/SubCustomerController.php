<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\SubCustomer;
use App\Http\Requests\StoreSubCustomerRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SubCustomerController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(SubCustomer::class, 'subCustomer');
    }

    /**
     * Show the form for adding a new sub-customer/location under the given parent customer.
     */
    public function create(Customer $customer): Response
    {
        return Inertia::render('SubCustomers/Create', [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
            ],
        ]);
    }

    /**
     * Store a newly created sub-customer/location under the given parent customer.
     */
    public function store(StoreSubCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $data = $request->validated();
        $data['customer_id'] = $customer->id;

        SubCustomer::create($data);

        return redirect()->route('customers.show', $customer)->with('success', 'Sub-customer added successfully.');
    }

    /**
     * Display the specified sub-customer/location with its own payment history.
     */
    public function show(SubCustomer $subCustomer): Response
    {
        $payments = $subCustomer->payments()->orderBy('id', 'desc')->get();

        return Inertia::render('SubCustomers/Show', [
            'customer' => [
                'id' => $subCustomer->customer->id,
                'name' => $subCustomer->customer->name,
            ],
            'subCustomer' => [
                'id' => $subCustomer->id,
                'name' => $subCustomer->name,
                'email' => $subCustomer->email,
                'phone' => $subCustomer->phone,
                'address' => $subCustomer->address,
                'status' => $subCustomer->status,
            ],
            'payments' => $payments,
            'metrics' => [
                'payments_count' => $payments->count(),
                'revenue' => $payments->where('status', 'successful')->sum('amount'),
                'outstanding' => $payments->where('status', 'pending')->sum('amount'),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified sub-customer/location.
     */
    public function edit(SubCustomer $subCustomer): Response
    {
        return Inertia::render('SubCustomers/Edit', [
            'customer' => [
                'id' => $subCustomer->customer->id,
                'name' => $subCustomer->customer->name,
            ],
            'subCustomer' => [
                'id' => $subCustomer->id,
                'name' => $subCustomer->name,
                'email' => $subCustomer->email,
                'phone' => $subCustomer->phone,
                'address' => $subCustomer->address,
                'status' => $subCustomer->status,
            ],
        ]);
    }

    /**
     * Update the specified sub-customer/location in storage.
     */
    public function update(StoreSubCustomerRequest $request, SubCustomer $subCustomer): RedirectResponse
    {
        $data = $request->validated();
        // Parent customer of a sub-customer cannot be changed after creation.
        $data['customer_id'] = $subCustomer->customer_id;

        $subCustomer->update($data);

        return redirect()->route('customers.show', $subCustomer->customer_id)->with('success', 'Sub-customer updated successfully.');
    }

    /**
     * Remove the specified sub-customer/location from storage, guarding against
     * accidental deletion when financial history exists.
     */
    public function destroy(SubCustomer $subCustomer): RedirectResponse
    {
        if ($subCustomer->payments()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete this sub-customer: it has related payment records. Remove or reassign those first.');
        }

        $customerId = $subCustomer->customer_id;
        $subCustomer->delete();

        return redirect()->route('customers.show', $customerId)->with('success', 'Sub-customer deleted successfully.');
    }
}
