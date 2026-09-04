<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Fill in customer_id from the route's {customer} or {subCustomer} binding
     * when it isn't already present in the request body, so nested routes
     * (which scope the parent implicitly) don't force the frontend to resend it.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('customer_id')) {
            return;
        }

        $routeCustomer = $this->route('customer');
        if ($routeCustomer) {
            $this->merge(['customer_id' => is_object($routeCustomer) ? $routeCustomer->id : $routeCustomer]);
            return;
        }

        $routeSubCustomer = $this->route('subCustomer');
        if ($routeSubCustomer && is_object($routeSubCustomer)) {
            $this->merge(['customer_id' => $routeSubCustomer->customer_id]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'status' => ['nullable', 'in:active,inactive'],
        ];
    }
}
