<?php

namespace App\Traits;

use App\Scopes\StrictTenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Tenant;

trait BelongsToTenant
{
    /**
     * Boot the strict tenant scoping trait.
     */
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new StrictTenantScope());

        static::creating(function ($model) {
            if (!$model->getAttribute('tenant_id') && tenancy()->initialized) {
                $model->setAttribute('tenant_id', tenancy()->tenant->id);
            }
        });
    }

    /**
     * Relationship with the Tenant model.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
