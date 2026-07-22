<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOpportunityRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'lead_id' => ['nullable', 'exists:leads,id'],
            'customer_id' => ['required', 'exists:customers,id'],
            'title' => ['required', 'string', 'max:150'],
            'pipeline_stage_id' => ['required', 'exists:crm_pipeline_stages,id'],
            'deal_value' => ['required', 'numeric', 'min:0'],
            'expected_close_date' => ['required', 'date'],
            'assigned_to' => ['required', 'exists:users,id'],
        ];
    }
}
