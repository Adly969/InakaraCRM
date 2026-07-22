<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-invoices');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sales_order_id' => ['nullable', 'exists:sales_orders,id'],
            'delivery_order_id' => ['nullable', 'exists:delivery_orders,id'],
            'customer_id' => ['required', 'exists:customers,id'],
            'invoice_date' => ['nullable', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:invoice_date'],
            'payment_term_code' => ['required', 'string', 'max:50'],
            'currency' => ['required', 'string', 'max:10'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0.000001'],
            'billing_address' => ['nullable', 'string', 'max:1000'],
            'shipping_address' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.sales_order_item_id' => ['nullable', 'exists:sales_order_items,id'],
            'items.*.delivery_order_item_id' => ['nullable', 'exists:delivery_order_items,id'],
            'items.*.sku' => ['required', 'string', 'max:100'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'adjustments' => ['nullable', 'array'],
            'adjustments.*.type' => ['required', 'string', 'max:50'],
            'adjustments.*.description' => ['required', 'string', 'max:255'],
            'adjustments.*.amount' => ['required', 'numeric'],
            'adjustments.*.is_taxable' => ['nullable', 'boolean'],
        ];
    }
}
