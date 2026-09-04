<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\LogsActivity;

class EmailTemplate extends Model
{
    use LogsActivity;
    protected $fillable = [
        'name',
        'description',
        'subject',
        'body',
    ];
}
