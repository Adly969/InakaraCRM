<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreShipmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->hasPermissionTo('dispatch-shipments');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'courier_type' => ['required', 'string', 'in:internal,third_party,expedition,pickup'],
            'carrier_id' => ['nullable', 'required_if:courier_type,third_party,expedition', 'integer', 'exists:carriers,id'],
            'driver_id' => ['nullable', 'required_if:courier_type,internal', 'integer', 'exists:drivers,id'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'estimated_cost' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0.000001'],
            'estimated_delivery_date' => ['nullable', 'date', 'after_or_equal:today'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.delivery_order_item_id' => ['required', 'integer', 'exists:delivery_order_items,id'],
            'items.*.quantity_shipped' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
