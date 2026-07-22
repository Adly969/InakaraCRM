<?php

namespace App\Http\Requests;

use App\Enums\ProductionPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreProductionOrderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'subject' => ['required', 'string', 'max:255'],
            'priority' => ['nullable', new Enum(ProductionPriority::class)],
            'target_completion_date' => ['nullable', 'date', 'after:today'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'production_notes' => ['nullable', 'string'],
            'currency' => ['required', 'string', 'size:3'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sales_order_item_id' => ['nullable', 'exists:sales_order_items,id'],
            'items.*.sort_order' => ['nullable', 'integer'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
