<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;

class ActivityLog extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'user_name',
        'action',
        'model_type',
        'reference_id',
        'message',
        'details',
    ];
}
