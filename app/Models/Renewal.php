<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\LogsActivity;
use App\Traits\BelongsToTenant;

class Renewal extends Model
{
    use HasFactory, LogsActivity, BelongsToTenant;

    protected $fillable = [
        'customer_name',
        'type',
        'plan_asset',
        'current_value',
        'renewal_date',
        'days_left',
        'priority',
        'renewal_reminders',
        'tenant_id',
    ];
}
