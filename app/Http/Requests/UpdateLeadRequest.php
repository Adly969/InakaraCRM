<?php

namespace App\Http\Requests;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateLeadRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'source' => ['required', new Enum(LeadSource::class)],
            'status' => ['required', new Enum(LeadStatus::class)],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'disqualification_reason' => [
                'required_if:status,'.LeadStatus::Disqualified->value,
                'nullable',
                'string',
            ],
        ];
    }
}
