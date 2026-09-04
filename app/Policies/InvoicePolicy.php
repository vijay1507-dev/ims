<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Invoice;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('invoices.view');
    }

    public function view(User $user, Invoice $invoice)
    {
        return $user->hasPermissionTo('invoices.view');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('invoices.create');
    }

    public function update(User $user, Invoice $invoice)
    {
        return $user->hasPermissionTo('invoices.edit');
    }

    public function delete(User $user, Invoice $invoice)
    {
        return $user->hasPermissionTo('invoices.delete');
    }
}
