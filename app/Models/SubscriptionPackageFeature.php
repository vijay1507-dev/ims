<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPackageFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'feature_id',
        'limit_type',
        'limit_value',
    ];

    public function feature(): BelongsTo
    {
        return $this->belongsTo(SubscriptionFeature::class, 'feature_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPackage::class, 'package_id');
    }
}
