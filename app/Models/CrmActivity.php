<?php

namespace App\Models;

use App\Enums\ActivityOutcome;
use App\Enums\CrmActivityStatus;
use App\Enums\CrmActivityType;
use App\Enums\TaskPriority;
use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property CrmActivityType $activity_type
 * @property string $subject
 * @property string|null $description
 * @property Carbon $start_time
 * @property Carbon|null $end_time
 * @property CrmActivityStatus $status
 * @property ActivityOutcome|null $outcome
 * @property TaskPriority $priority
 * @property string|null $location
 * @property int|null $duration_minutes
 * @property Carbon|null $reminder_at
 * @property bool $is_recurring
 * @property string|null $recurrence_rule
 * @property int|null $lead_id
 * @property int|null $customer_id
 * @property int|null $opportunity_id
 * @property int|null $assigned_to
 * @property int $created_by
 * @property int|null $updated_by
 * @property int $version
 * @property int|null $company_id
 * @property int|null $branch_id
 */
#[Fillable([
    'activity_type', 'subject', 'description', 'start_time', 'end_time',
    'status', 'outcome', 'priority', 'location', 'duration_minutes',
    'reminder_at', 'is_recurring', 'recurrence_rule',
    'lead_id', 'customer_id', 'opportunity_id',
    'assigned_to', 'created_by', 'updated_by',
    'company_id', 'branch_id',
])]
class CrmActivity extends Model
{
    use HasTenantIsolation;
    use SoftDeletes;

    protected $table = 'crm_activities';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activity_type' => CrmActivityType::class,
            'status' => CrmActivityStatus::class,
            'outcome' => ActivityOutcome::class,
            'priority' => TaskPriority::class,
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'reminder_at' => 'datetime',
            'is_recurring' => 'boolean',
        ];
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

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<User, $this> */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** @return HasMany<CrmActivityComment, $this> */
    public function activityComments(): HasMany
    {
        return $this->hasMany(CrmActivityComment::class, 'activity_id');
    }

    /** @return HasMany<CrmActivityAttachment, $this> */
    public function attachments(): HasMany
    {
        return $this->hasMany(CrmActivityAttachment::class, 'activity_id');
    }

    /** @return MorphMany<CrmComment, $this> */
    public function comments(): MorphMany
    {
        return $this->morphMany(CrmComment::class, 'commentable');
    }

    /** @return MorphOne<CrmReminder, $this> */
    public function reminder(): MorphOne
    {
        return $this->morphOne(CrmReminder::class, 'remindable');
    }

    /** @return MorphToMany<CrmTag, $this> */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(CrmTag::class, 'taggable', 'crm_taggables', 'taggable_id', 'tag_id');
    }
}
