<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreDeliveryOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->hasPermissionTo('create-delivery-orders');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sales_order_id' => ['required', 'integer', 'exists:sales_orders,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'company_id' => ['nullable', 'integer'],
            'branch_id' => ['nullable', 'integer'],
            'shipping_address' => ['nullable', 'string', 'max:1000'],
            'billing_address' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sales_order_item_id' => ['required', 'integer', 'exists:sales_order_items,id'],
            'items.*.quantity_requested' => ['required', 'numeric', 'min:0.01'],
            'items.*.sort_order' => ['nullable', 'integer'],
        ];
    }
}
