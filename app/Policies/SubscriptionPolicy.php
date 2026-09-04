<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubscriptionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('subscriptions.view');
    }

    public function view(User $user, Subscription $subscription)
    {
        return $user->hasPermissionTo('subscriptions.view');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('subscriptions.create');
    }

    public function update(User $user, Subscription $subscription)
    {
        return $user->hasPermissionTo('subscriptions.edit');
    }

    public function delete(User $user, Subscription $subscription)
    {
        return $user->hasPermissionTo('subscriptions.delete');
    }
}
