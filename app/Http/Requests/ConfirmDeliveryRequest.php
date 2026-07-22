<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ConfirmDeliveryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->hasPermissionTo('confirm-deliveries');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'receiver_name' => ['required', 'string', 'max:100'],
            'receiver_signature' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
            'gps_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'gps_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'actual_cost' => ['required', 'numeric', 'min:0'],
        ];
    }
}
