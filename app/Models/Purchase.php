<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;
use App\Traits\BelongsToTenant;

class Purchase extends Model
{
    use HasFactory, LogsActivity, BelongsToTenant;

    protected $fillable = [
        'supplier_name',
        'purchase_number',
        'purchase_date',
        'total_amount',
        'status',
        'payment_status',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($purchase) {
            if (empty($purchase->purchase_number)) {
                $year = date('Y');
                $latest = static::withoutGlobalScopes()->where('purchase_number', 'like', "PO-{$year}-%")->latest('id')->first();
                $num = $latest ? ((int) substr($latest->purchase_number, -4)) + 1 : 1;
                do {
                    $numStr = sprintf('PO-%s-%04d', $year, $num);
                    $exists = static::withoutGlobalScopes()->where('purchase_number', $numStr)->exists();
                    if ($exists) {
                        $num++;
                    }
                } while ($exists);
                $purchase->purchase_number = $numStr;
            }
        });
    }

    /**
     * Get the items for this purchase.
     */
    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Get the user who created this purchase.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
