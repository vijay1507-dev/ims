<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\LogsActivity;
use App\Traits\BelongsToTenant;

class EmailTemplate extends Model
{
    use LogsActivity, BelongsToTenant;
    protected $fillable = [
        'name',
        'description',
        'subject',
        'body',
    ];
}
