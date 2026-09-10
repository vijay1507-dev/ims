<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;

class Commission extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'employee_name',
        'user_id',
        'month',
        'year',
        'salary',
        'total_payments_closed',
        'achievement_percentage',
        'points',
        'commission_slab_id',
        'commission_percentage',
        'commission_amount',
        'calculation_date',
        'status',
    ];

    /**
     * Get the user/employee associated with the commission.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the commission slab associated with the commission.
     */
    public function slab()
    {
        return $this->belongsTo(CommissionSlab::class, 'commission_slab_id');
    }
}
