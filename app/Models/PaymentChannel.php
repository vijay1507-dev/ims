<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class PaymentChannel extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the payments associated with the payment channel.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'method_type', 'code');
    }
}
