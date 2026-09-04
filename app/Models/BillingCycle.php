<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingCycle extends Model
{
    protected $fillable = [
        'name',
        'code',
        'duration_months',
        'status',
    ];
}
