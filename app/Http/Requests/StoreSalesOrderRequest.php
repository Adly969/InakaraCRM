<?php

namespace App\Http\Requests;

use App\Enums\SalesOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreSalesOrderRequest extends FormRequest
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
            'quotation_id' => ['nullable', 'exists:quotations,id'],
            'subject' => ['required', 'string', 'max:255'],
            'status' => ['nullable', new Enum(SalesOrderStatus::class)],
            'delivery_terms' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'currency' => ['required', 'string', 'size:3'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
