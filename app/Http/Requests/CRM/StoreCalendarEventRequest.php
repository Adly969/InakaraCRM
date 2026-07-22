<?php

namespace App\Http\Requests\CRM;

use App\Enums\CalendarEventType;
use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreCalendarEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permission::CreateCalendarEvents->value) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'event_type' => ['required', new Enum(CalendarEventType::class)],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'all_day' => ['nullable', 'boolean'],
            'location' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:7'],
            'allow_overlap' => ['nullable', 'boolean'],
            'lead_id' => ['nullable', 'exists:leads,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'opportunity_id' => ['nullable', 'exists:crm_opportunities,id'],
            'activity_id' => ['nullable', 'exists:crm_activities,id'],
            'attendees' => ['nullable', 'array'],
            'attendees.*.user_id' => ['nullable', 'exists:users,id'],
            'attendees.*.external_name' => ['nullable', 'string', 'max:100'],
            'attendees.*.external_email' => ['nullable', 'email', 'max:150'],
        ];
    }
}
