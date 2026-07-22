<?php

namespace App\Http\Requests;

use App\Enums\OpportunityStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateOpportunityRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'pipeline_stage_id' => ['required', 'exists:crm_pipeline_stages,id'],
            'status' => ['required', new Enum(OpportunityStatus::class)],
            'deal_value' => ['required', 'numeric', 'min:0'],
            'expected_close_date' => ['required', 'date'],
            'assigned_to' => ['required', 'exists:users,id'],
            'loss_reason_id' => ['required_if:status,'.OpportunityStatus::Lost->value, 'nullable', 'exists:crm_loss_reasons,id'],
            'loss_notes' => ['nullable', 'string'],
        ];
    }
}
