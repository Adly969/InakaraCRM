<?php

namespace App\Models;

use App\Enums\CalendarEventStatus;
use App\Enums\CalendarEventType;
use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class CalendarEvent extends Model
{
    use HasTenantIsolation;
    use SoftDeletes;

    protected $table = 'crm_calendar_events';

    protected $fillable = [
        'title',
        'description',
        'event_type',
        'start_at',
        'end_at',
        'all_day',
        'location',
        'color',
        'is_recurring',
        'recurrence_rule',
        'activity_id',
        'lead_id',
        'customer_id',
        'opportunity_id',
        'organizer_id',
        'status',
        'company_id',
        'branch_id',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => CalendarEventType::class,
            'status' => CalendarEventStatus::class,
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'all_day' => 'boolean',
            'is_recurring' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    /** @return BelongsTo<CrmActivity, $this> */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(CrmActivity::class, 'activity_id');
    }

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return BelongsTo<Opportunity, $this> */
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    /** @return HasMany<MeetingAttendee, $this> */
    public function attendees(): HasMany
    {
        return $this->hasMany(MeetingAttendee::class, 'calendar_event_id');
    }

    /** @return MorphOne<CrmReminder, $this> */
    public function reminder(): MorphOne
    {
        return $this->morphOne(CrmReminder::class, 'remindable');
    }
}
