<?php

namespace App\Services\CRM;

use App\Models\CalendarEvent;
use App\Models\MeetingAttendee;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CalendarService
{
    /**
     * Get events for a date range (for calendar view).
     *
     * @return Collection<int, CalendarEvent>
     */
    public function getEvents(Carbon $start, Carbon $end, ?int $organizerId = null): Collection
    {
        $query = CalendarEvent::query()
            ->with(['organizer:id,name', 'customer:id,name', 'lead:id,first_name,last_name', 'opportunity:id,title', 'attendees.user:id,name'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_at', [$start, $end])
                    ->orWhereBetween('end_at', [$start, $end]);
            })
            ->orderBy('start_at', 'asc');

        if ($organizerId) {
            $query->where('organizer_id', $organizerId);
        }

        return $query->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $organizer): CalendarEvent
    {
        // Conflict detection check
        $conflict = CalendarEvent::query()
            ->where('organizer_id', $organizer->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($data) {
                $q->whereBetween('start_at', [$data['start_at'], $data['end_at']])
                    ->orWhereBetween('end_at', [$data['start_at'], $data['end_at']]);
            })
            ->exists();

        if ($conflict && empty($data['allow_overlap'])) {
            throw ValidationException::withMessages([
                'start_at' => ['You already have another meeting scheduled during this time slot.'],
            ]);
        }

        return DB::transaction(function () use ($data, $organizer) {
            $event = CalendarEvent::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'event_type' => $data['event_type'],
                'start_at' => $data['start_at'],
                'end_at' => $data['end_at'],
                'all_day' => $data['all_day'] ?? false,
                'location' => $data['location'] ?? null,
                'color' => $data['color'] ?? null,
                'is_recurring' => $data['is_recurring'] ?? false,
                'recurrence_rule' => $data['recurrence_rule'] ?? null,
                'activity_id' => $data['activity_id'] ?? null,
                'lead_id' => $data['lead_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'opportunity_id' => $data['opportunity_id'] ?? null,
                'organizer_id' => $organizer->id,
                'status' => $data['status'] ?? 'confirmed',
                'company_id' => $organizer->company_id,
                'branch_id' => $organizer->branch_id,
            ]);

            if (! empty($data['attendees']) && is_array($data['attendees'])) {
                foreach ($data['attendees'] as $attendeeData) {
                    MeetingAttendee::create([
                        'calendar_event_id' => $event->id,
                        'user_id' => $attendeeData['user_id'] ?? null,
                        'external_name' => $attendeeData['external_name'] ?? null,
                        'external_email' => $attendeeData['external_email'] ?? null,
                        'rsvp_status' => 'pending',
                    ]);
                }
            }

            return $event->fresh(['attendees.user']);
        });
    }

    /**
     * Reschedule or update event.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(CalendarEvent $event, array $data, User $updater): CalendarEvent
    {
        if (isset($data['version']) && (int) $data['version'] !== $event->version) {
            throw ValidationException::withMessages([
                'version' => ['This calendar event has been modified by another user. Please refresh.'],
            ]);
        }

        return DB::transaction(function () use ($event, $data) {
            $event->update(array_merge($data, [
                'version' => $event->version + 1,
            ]));

            return $event->fresh(['attendees.user']);
        });
    }
}
