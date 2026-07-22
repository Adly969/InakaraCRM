<?php

namespace App\Http\Requests;

use App\Enums\QuotationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateQuotationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required_without:lead_id', 'nullable', 'prohibits:lead_id', 'exists:customers,id'],
            'lead_id' => ['required_without:customer_id', 'nullable', 'prohibits:customer_id', 'exists:leads,id'],
            'subject' => ['required', 'string', 'max:255'],
            'status' => ['nullable', new Enum(QuotationStatus::class)],
            'valid_until' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'currency' => ['required', 'string', 'size:3'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'exists:quotation_items,id'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
