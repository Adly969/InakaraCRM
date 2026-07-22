<?php

namespace App\Http\Requests\CRM;

use App\Enums\ActivityOutcome;
use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CompleteActivityRequest extends FormRequest
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
            'outcome' => ['required', new Enum(ActivityOutcome::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
