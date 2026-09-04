<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Renewal;
use Illuminate\Auth\Access\HandlesAuthorization;

class RenewalPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('renewals.view');
    }

    public function view(User $user, Renewal $renewal)
    {
        return $user->hasPermissionTo('renewals.view');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('renewals.create');
    }

    public function update(User $user, Renewal $renewal)
    {
        return $user->hasPermissionTo('renewals.edit');
    }

    public function delete(User $user, Renewal $renewal)
    {
        return $user->hasPermissionTo('renewals.delete');
    }
}
