<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;
use App\Traits\BelongsToTenant;

class Invoice extends Model
{
    use HasFactory, LogsActivity, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'invoice_number',
        'customer_name',
        'amount',
        'tax',
        'total',
        'invoice_date',
        'due_date',
        'status',
        'billing_cycle',
        'currency',
        'usd_amount',
        'closed_by',
    ];

    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format((float) $this->amount, 2);
    }

    public function getFormattedTaxAttribute(): string
    {
        return '$' . number_format((float) $this->tax, 2);
    }

    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format((float) $this->total, 2);
    }
}
