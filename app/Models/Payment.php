<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;
use App\Traits\BelongsToTenant;

class Payment extends Model
{
    use HasFactory, LogsActivity, SoftDeletes, BelongsToTenant;

    protected static function booted()
    {
        static::updated(function ($payment) {
            // Skip sync if the payment is being soft-deleted
            if ($payment->isDirty('deleted_at')) {
                return;
            }

            $originalInvoiceRef = $payment->getOriginal('invoice_ref');
            
            if ($originalInvoiceRef) {
                $invoice = Invoice::where('invoice_number', $originalInvoiceRef)->first();
                if ($invoice) {
                    $invoiceCustomerName = $payment->customer_name;
                    if ($payment->sub_customer_id) {
                        $subCustomer = SubCustomer::find($payment->sub_customer_id);
                        if ($subCustomer) {
                            $invoiceCustomerName = $payment->customer_name . ' - ' . $subCustomer->name;
                        }
                    }

                    $invoiceStatus = 'pending';
                    if ($payment->status === 'successful') {
                        $invoiceStatus = 'paid';
                    } elseif ($payment->status === 'failed' || $payment->status === 'refunded') {
                        $invoiceStatus = 'draft';
                    }

                    $invoiceDate = $payment->payment_date ?: now()->format('M d, Y');
                    $dueDate = date('M d, Y', strtotime($invoiceDate . ' + 14 days'));

                    $invoice->update([
                        'invoice_number' => $payment->invoice_ref ?? $invoice->invoice_number,
                        'customer_name' => $invoiceCustomerName,
                        'amount' => $payment->amount,
                        'total' => $payment->amount + $invoice->tax,
                        'invoice_date' => $invoiceDate,
                        'due_date' => $dueDate,
                        'status' => $invoiceStatus,
                        'billing_cycle' => $payment->billing_cycle,
                        'currency' => $payment->currency ?? 'USD',
                        'usd_amount' => $payment->usd_amount ?? 0.00,
                        'closed_by' => $payment->closed_by,
                    ]);
                }
            }
        });
    }

    protected $fillable = [
        'transaction_id',
        'customer_id',
        'customer_name',
        'amount',
        'payment_method',
        'method_type',
        'status',
        'invoice_ref',
        'payment_date',
        'billing_cycle',
        'currency',
        'usd_amount',
        'end_date',
        'payment_context',
        'sub_customer_id',
        'closed_by',
    ];

    /**
     * Get the customer associated with the payment.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the sub-customer/location associated with the payment, if any.
     */
    public function subCustomer()
    {
        return $this->belongsTo(SubCustomer::class, 'sub_customer_id');
    }

    /**
     * Get automatically formatted transactional amounts with prefixed currencies.
     */
    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format((float) $this->amount, 2);
    }
}
