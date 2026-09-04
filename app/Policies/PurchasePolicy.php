<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Purchase;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchasePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Purchase $purchase)
    {
        return true;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Purchase $purchase)
    {
        return true;
    }

    public function delete(User $user, Purchase $purchase)
    {
        return true;
    }
}
