<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreGoodsIssueRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sales_order_id' => ['nullable', 'exists:sales_orders,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'issued_date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string'],
            'remark' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sales_order_item_id' => ['nullable', 'exists:sales_order_items,id'],
            'items.*.sku' => ['required', 'string', 'max:100'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity_issued' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['required', 'string', 'max:50'],
        ];
    }
}
