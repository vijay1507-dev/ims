<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Payment;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('payments.view');
    }

    public function view(User $user, Payment $payment)
    {
        return $user->hasPermissionTo('payments.view');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('payments.create');
    }

    public function update(User $user, Payment $payment)
    {
        return $user->hasPermissionTo('payments.edit');
    }

    public function delete(User $user, Payment $payment)
    {
        return $user->hasPermissionTo('payments.delete');
    }

    public function refund(User $user, Payment $payment)
    {
        return $user->hasPermissionTo('payments.edit');
    }
}
