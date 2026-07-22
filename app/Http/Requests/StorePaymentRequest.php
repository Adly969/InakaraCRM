<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethodType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-payments');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'payment_method' => ['required', 'string', Rule::in(PaymentMethodType::values())],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999999.99'],
            'currency' => ['required', 'string', 'max:10'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0.000001'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_no' => ['nullable', 'string', 'max:100'],
            'cheque_no' => ['nullable', 'string', 'max:100'],
            'transaction_ref' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'allocations' => ['nullable', 'array', 'max:50'],
            'allocations.*.invoice_id' => ['required', 'exists:invoices,id'],
            'allocations.*.amount' => ['required', 'numeric', 'min:0.01'],
            'allocations.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
