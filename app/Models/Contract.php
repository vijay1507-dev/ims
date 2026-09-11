<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;
use App\Traits\BelongsToTenant;

class Contract extends Model
{
    use HasFactory, LogsActivity, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'contract_number',
        'subject',
        'value',
        'start_date',
        'end_date',
        'status',
        'contract_type_id',
        'customer_id',
        'customer_name',
        'attachment_path',
        'attachment_name',
        'created_by',
        'updated_by',
    ];

    protected $appends = ['attachment_url'];

    public function getAttachmentUrlAttribute()
    {
        return $this->attachment_path ? route('contracts.attachment', $this->id) : null;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($contract) {
            if (empty($contract->contract_number)) {
                $lastContract = static::withoutGlobalScopes()->withTrashed()->orderBy('id', 'desc')->first();
                $nextNum = 1;
                if ($lastContract && preg_match('/CON(\d+)/i', $lastContract->contract_number, $matches)) {
                    $nextNum = ((int)$matches[1]) + 1;
                } else {
                    $count = static::withoutGlobalScopes()->withTrashed()->count();
                    $nextNum = $count + 1;
                }
                // Loop to make absolutely sure it's unique across all tenants & soft-deleted records
                do {
                    $numStr = 'CON' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
                    $exists = static::withoutGlobalScopes()->withTrashed()->where('contract_number', $numStr)->exists();
                    if ($exists) {
                        $nextNum++;
                    }
                } while ($exists);

                $contract->contract_number = $numStr;
            }
        });
    }

    /**
     * Get the contract type associated with the contract.
     */
    public function contractType()
    {
        return $this->belongsTo(ContractType::class, 'contract_type_id');
    }

    /**
     * Get the customer associated with the contract.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
