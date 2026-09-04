<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_avatar' => ['nullable', 'string', 'url', 'max:500'],
            'start_date' => ['nullable', 'string', 'max:50'],
            'plan_name' => ['required', 'string', 'max:100'],
            'billing_cycle' => ['required', 'in:monthly,annual,two_months,half_yearly,quarterly,evently'],
            'next_billing_date' => ['nullable', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,trial,paused,cancelled'],
            'redirect_customer_id' => ['nullable', 'integer'],
            'sub_customer_id' => ['nullable', 'integer', 'exists:sub_customers,id'],
        ];
    }
}
