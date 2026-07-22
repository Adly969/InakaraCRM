<?php

namespace App\Models;

use App\Enums\RsvpStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingAttendee extends Model
{
    public $timestamps = false;

    protected $table = 'crm_meeting_attendees';

    protected $fillable = [
        'calendar_event_id',
        'user_id',
        'external_name',
        'external_email',
        'rsvp_status',
    ];

    protected function casts(): array
    {
        return [
            'rsvp_status' => RsvpStatus::class,
        ];
    }

    /** @return BelongsTo<CalendarEvent, $this> */
    public function calendarEvent(): BelongsTo
    {
        return $this->belongsTo(CalendarEvent::class, 'calendar_event_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
