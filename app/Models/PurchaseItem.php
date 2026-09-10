<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;

class PurchaseItem extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'purchase_id',
        'item_name',
        'category',
        'quantity',
        'unit_cost',
        'subtotal',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Get the purchase order for this item.
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
