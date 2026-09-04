<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\LogsActivity;

class SubscriptionPricing extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'subscription_pricing';

    protected $fillable = [
        'package_id',
        'currency',
        'billing_cycle',
        'price',
        'status',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPackage::class, 'package_id');
    }
}
