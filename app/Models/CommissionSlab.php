<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionSlab extends Model
{
    use HasFactory;

    protected $fillable = [
        'achievement_amount',
        'achievement_percentage',
        'points',
        'commission_percentage',
        'status',
    ];

    /**
     * Scope to only include active slabs.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
