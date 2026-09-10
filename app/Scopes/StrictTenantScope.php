<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class StrictTenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (tenancy()->initialized) {
            $builder->where($model->getTable() . '.tenant_id', tenancy()->tenant->id);
        } else {
            // Central platform: All users are visible on central dashboard, but CRM models remain isolated
            if ($model instanceof \App\Models\User) {
                return;
            }

            $builder->whereNull($model->getTable() . '.tenant_id');
        }
    }
}
