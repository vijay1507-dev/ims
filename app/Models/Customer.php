<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class Customer extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'contact_name',
        'email',
        'phone',
        'address',
        'role',
        'industry',
        'country',
        'message',
        'avatar',
        'plan',
        'status',
        'mrr',
        'joined_date',
    ];

    /**
     * Get a formatted MRR display string.
     */
    public function getFormattedMrrAttribute(): string
    {
        return '$' . number_format((float) $this->mrr, 0);
    }

    /**
     * Sub-customers/locations belonging to this main (parent) customer.
     */
    public function subCustomers()
    {
        return $this->hasMany(SubCustomer::class);
    }

    /**
     * Payments recorded directly against this customer (parent-level and
     * sub-customer payments alike, since sub-customer payments also stamp
     * the parent customer_id).
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'customer_id');
    }
}
