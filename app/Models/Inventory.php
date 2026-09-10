<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;
use App\Traits\BelongsToTenant;

class Inventory extends Model
{
    use HasFactory, LogsActivity, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'asset_id',
        'category',
        'type_name',
        'name_model',
        'customer_name',
        'serial_license',
        'assigned_date',
        'warranty_expiry',
        'status',
    ];
}
