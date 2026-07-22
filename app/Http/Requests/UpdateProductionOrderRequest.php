<?php

namespace App\Http\Requests;

use App\Enums\ProductionOrderStatus;
use App\Enums\ProductionPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateProductionOrderRequest extends FormRequest
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
            'target_completion_date' => [
                'nullable',
                'required_if:status,scheduled',
                'date',
            ],
            'actual_completion_date' => ['nullable', 'date'],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'actual_hours' => ['nullable', 'numeric', 'min:0'],
            'production_notes' => ['nullable', 'string'],
            'cancellation_reason' => [
                'nullable',
                'required_if:status,cancelled',
                'string',
            ],
            'currency' => ['required', 'string', 'size:3'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            '_updated_at' => ['required', 'string'], // Sent as string from request header/body
            'status' => ['nullable', new Enum(ProductionOrderStatus::class)],

            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'exists:production_order_items,id'],
            'items.*.sales_order_item_id' => ['nullable', 'exists:sales_order_items,id'],
            'items.*.sort_order' => ['nullable', 'integer'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
