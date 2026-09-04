<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\LogsActivity;

class SubscriptionPackage extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'subtitle',
        'description',
        'status',
        'trial_days',
        'credit_card_required',
        'monthly_enabled',
        'annual_enabled',
        'sort_order',
        'is_most_popular',
    ];

    public function pricing(): HasMany
    {
        return $this->hasMany(SubscriptionPricing::class, 'package_id');
    }

    public function packageFeatures(): HasMany
    {
        return $this->hasMany(SubscriptionPackageFeature::class, 'package_id');
    }
}
