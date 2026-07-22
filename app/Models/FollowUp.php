<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $customer_id
 * @property string $tenant_id
 * @property int|null $activity_id
 * @property string $title
 * @property string|null $description
 * @property string $due_date
 * @property string $priority
 * @property string $status
 * @property int|null $assigned_to
 * @property string|null $completed_at
 * @property int|null $created_by
 */
#[Fillable([
    'customer_id',
    'activity_id',
    'title',
    'description',
    'due_date',
    'priority',
    'status',
    'assigned_to',
    'completed_at',
    'created_by',
])]
class FollowUp extends Model
{
    use HasFactory, HasTenantIsolation;

    /**
     * Get the customer associated with the task.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the timeline activity that triggered this follow-up.
     *
     * @return BelongsTo<Activity, $this>
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * Get the user assigned to the follow-up task.
     *
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
