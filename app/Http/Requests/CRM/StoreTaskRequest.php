<?php

namespace App\Http\Requests\CRM;

use App\Enums\Permission;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permission::CreateTasks->value) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', new Enum(TaskStatus::class)],
            'priority' => ['nullable', new Enum(TaskPriority::class)],
            'due_date' => ['required', 'date'],
            'due_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'opportunity_id' => ['nullable', 'exists:crm_opportunities,id'],
            'parent_task_id' => ['nullable', 'exists:crm_tasks,id'],
            'reminder_at' => ['nullable', 'date'],
            'checklists' => ['nullable', 'array'],
            'checklists.*' => ['required', 'string', 'max:200'],
        ];
    }
}
