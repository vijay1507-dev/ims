<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class BillingCycle extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'name',
        'code',
        'duration_months',
        'status',
    ];
}
