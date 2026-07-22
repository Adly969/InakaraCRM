<?php

namespace App\Http\Requests\CRM;

use App\Enums\CrmActivityType;
use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permission::EditActivities->value) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'activity_type' => ['sometimes', 'required', new Enum(CrmActivityType::class)],
            'subject' => ['sometimes', 'required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'start_time' => ['sometimes', 'required', 'date'],
            'end_time' => ['nullable', 'date', 'after_or_equal:start_time'],
            'status' => ['nullable', 'string', 'in:pending,completed,cancelled'],
            'priority' => ['nullable', 'string', 'in:low,medium,high,urgent'],
            'location' => ['nullable', 'string', 'max:255'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'reminder_at' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'version' => ['nullable', 'integer'],
        ];
    }
}
