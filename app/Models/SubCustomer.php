<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;
use App\Traits\BelongsToTenant;

class SubCustomer extends Model
{
    use HasFactory, LogsActivity, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'customer_id',
        'name',
        'email',
        'phone',
        'address',
        'status',
    ];

    /**
     * The main (parent) customer this location belongs to.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Payments recorded against this sub-customer/location.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
