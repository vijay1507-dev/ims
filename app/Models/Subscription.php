<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;
use App\Traits\BelongsToTenant;

class Subscription extends Model
{
    use HasFactory, LogsActivity, SoftDeletes, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_avatar',
        'start_date',
        'plan_name',
        'billing_cycle',
        'next_billing_date',
        'renewal_date',
        'auto_renewal',
        'amount',
        'status',
        'sub_customer_id',
    ];

    protected static function booted()
    {
        static::saved(function ($subscription) {
            $subscription->syncCustomer();
        });
    }

    public function syncCustomer()
    {
        $customer = Customer::where('email', $this->customer_email)
            ->orWhere('name', $this->customer_name)
            ->first();
 
        if ($customer) {
            $cleanPlan = trim($this->plan_name);
            if ($cleanPlan && !str_ends_with(strtolower($cleanPlan), 'plan')) {
                $customerPlan = $cleanPlan . ' Plan';
            } else {
                $customerPlan = $cleanPlan ?: 'Starter Plan';
            }
 
            // Starter -> trial, others -> active
            $isStarter = (stripos($cleanPlan, 'starter') !== false);
            $customerStatus = $isStarter ? 'trial' : 'active';
            $customerMrr = (float)$this->amount;
 
            // Only update if something has actually changed (prevents redundant updates and duplicate logs on refresh)
            if (
                trim($customer->plan) !== trim($customerPlan) ||
                trim($customer->status) !== trim($customerStatus) ||
                abs((float)$customer->mrr - $customerMrr) > 0.0001
            ) {
                $customer->update([
                    'plan' => $customerPlan,
                    'status' => $customerStatus,
                    'mrr' => $customerMrr,
                ]);
            }
        }
    }

    public static function syncAllCustomersWithSubscriptions()
    {
        $subscriptions = static::all();
        foreach ($subscriptions as $sub) {
            $sub->syncCustomer();
        }
    }

    /**
     * Get the sub-customer associated with the subscription, if any.
     */
    public function subCustomer()
    {
        return $this->belongsTo(SubCustomer::class);
    }

    /**
     * Get a formatted billing amount string.
     */
    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format((float) $this->amount, 2);
    }
}
