<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::created(function ($model) {
            $model->logActivity('Create');
        });

        static::updated(function ($model) {
            $model->logActivity('Update');
        });

        static::deleted(function ($model) {
            $model->logActivity('Delete');
        });
    }

    public function logActivity($action)
    {
        $user = Auth::user();
        $userName = $user ? $user->name : 'System';

        $modelClass = class_basename($this);
        $referenceId = $this->getActivityReferenceId();
        $message = '';
        $details = '';

        if ($action === 'Create') {
            $message = "{$userName} created {$modelClass} {$referenceId}";
            if ($modelClass === 'Invoice') {
                $message = "{$userName} created Invoice {$referenceId}";
                $details = "Amount: ₹" . number_format((float)$this->amount, 2);
            } elseif ($modelClass === 'Subscription') {
                $message = "{$userName} created Subscription for {$this->customer_name}";
                $details = "Plan: {$this->plan_name}\nAmount: $" . number_format((float)$this->amount, 2);
            } elseif ($modelClass === 'Customer') {
                $message = "{$userName} registered Customer {$this->name}";
                $details = "Plan: {$this->plan}";
            } elseif ($modelClass === 'SubCustomer') {
                $parentName = $this->customer?->name ?? 'Unknown';
                $message = "{$userName} added Sub-Customer {$this->name} under {$parentName}";
                $details = "Status: {$this->status}";
            } elseif ($modelClass === 'Payment') {
                $message = "{$userName} recorded Payment of $" . number_format((float)$this->amount, 2) . " for {$this->customer_name}";
            } elseif ($modelClass === 'Inventory') {
                $message = "{$userName} assigned Asset {$this->name_model} ({$this->asset_id}) to {$this->customer_name}";
                $details = "Category: {$this->category}\nStatus: {$this->status}";
            } elseif ($modelClass === 'EmailTemplate') {
                $message = "{$userName} created Email Template {$referenceId}";
                $details = "Subject: {$this->subject}";
            } elseif ($modelClass === 'Renewal') {
                $message = "{$userName} registered contract renewal for {$this->customer_name}";
                $details = "Plan/Asset: {$this->plan_asset}\nPriority: {$this->priority}";
            } elseif ($modelClass === 'SubscriptionPackage') {
                $message = "{$userName} created Pricing Package {$referenceId}";
                $details = "Code: {$this->code}\nTrial Days: {$this->trial_days}";
            } elseif ($modelClass === 'SubscriptionPricing') {
                $message = "{$userName} recorded new Pricing for Package";
                $details = "Billing Cycle: {$this->billing_cycle}\nPrice: {$this->currency} {$this->price}";
            } elseif ($modelClass === 'User') {
                $message = "{$userName} created User Account {$referenceId}";
                $details = "Email: {$this->email}";
            } elseif ($modelClass === 'Contract') {
                $message = "{$userName} created Contract {$this->contract_number}";
                $details = "Subject: {$this->subject}\nValue: $" . number_format((float)$this->value, 2);
            } elseif ($modelClass === 'ContractType') {
                $message = "{$userName} created Contract Type {$this->name}";
                $details = "Status: {$this->status}";
            }
        } elseif ($action === 'Update') {
            $message = "{$userName} updated {$modelClass} {$referenceId}";
            
            $dirtyFields = $this->getDirty();
            unset($dirtyFields['updated_at']);
            unset($dirtyFields['created_at']);
            unset($dirtyFields['id']);
            unset($dirtyFields['password']);
            unset($dirtyFields['remember_token']);
            unset($dirtyFields['two_factor_recovery_codes']);
            unset($dirtyFields['two_factor_secret']);

            $changeList = [];
            foreach ($dirtyFields as $field => $newValue) {
                $oldValue = $this->getOriginal($field);
                
                $fieldName = ucwords(str_replace('_', ' ', $field));
                
                if (is_null($oldValue) || $oldValue === '') {
                    $oldValue = 'None';
                }
                if (is_null($newValue) || $newValue === '') {
                    $newValue = 'None';
                }
                
                if (in_array($field, ['amount', 'total', 'tax'])) {
                    $symbol = ($modelClass === 'Invoice') ? '₹' : '$';
                    $oldValue = $symbol . number_format((float)$oldValue, 2);
                    $newValue = $symbol . number_format((float)$newValue, 2);
                }
                
                $changeList[] = "Updated {$fieldName}: {$oldValue} → {$newValue}";
            }

            if (!empty($changeList)) {
                $details = implode("\n", $changeList);
            } else {
                $details = "Saved without changes";
            }
        } elseif ($action === 'Delete') {
            $message = "{$userName} deleted {$modelClass} {$referenceId}";
            if ($modelClass === 'Invoice') {
                $message = "{$userName} deleted Invoice {$referenceId}";
            } elseif ($modelClass === 'Subscription') {
                $message = "{$userName} deleted Subscription for {$this->customer_name}";
            } elseif ($modelClass === 'Customer') {
                $message = "{$userName} deleted Customer {$this->name}";
            } elseif ($modelClass === 'SubCustomer') {
                $message = "{$userName} deleted Sub-Customer {$this->name}";
            } elseif ($modelClass === 'Inventory') {
                $message = "{$userName} retired Asset {$this->name_model} ({$this->asset_id})";
            } elseif ($modelClass === 'EmailTemplate') {
                $message = "{$userName} deleted Email Template {$referenceId}";
            } elseif ($modelClass === 'Renewal') {
                $message = "{$userName} cleared Renewal contract for {$referenceId}";
            } elseif ($modelClass === 'SubscriptionPackage') {
                $message = "{$userName} deleted Pricing Package {$referenceId}";
            } elseif ($modelClass === 'SubscriptionPricing') {
                $message = "{$userName} deleted Pricing for Package";
            } elseif ($modelClass === 'User') {
                $message = "{$userName} deleted User Account {$referenceId}";
            } elseif ($modelClass === 'Contract') {
                $message = "{$userName} deleted Contract {$this->contract_number}";
            } elseif ($modelClass === 'ContractType') {
                $message = "{$userName} deleted Contract Type {$this->name}";
            }
        }

        ActivityLog::create([
            'user_name' => $userName,
            'action' => $action,
            'model_type' => $modelClass,
            'reference_id' => $referenceId,
            'message' => $message,
            'details' => $details ?: null,
        ]);
    }

    public function getActivityReferenceId()
    {
        if (isset($this->contract_number)) {
            return $this->contract_number;
        }
        if (isset($this->invoice_number)) {
            return $this->invoice_number;
        }
        if (isset($this->transaction_id)) {
            return $this->transaction_id;
        }
        if (isset($this->asset_id)) {
            return $this->asset_id;
        }
        if (isset($this->customer_name)) {
            return $this->customer_name;
        }
        if (isset($this->name)) {
            return $this->name;
        }
        return '#' . $this->id;
    }
}
