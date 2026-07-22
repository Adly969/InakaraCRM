<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int|null $customer_id
 * @property int|null $lead_id
 * @property string $tenant_id
 * @property string $type
 * @property string $subject
 * @property string|null $description
 * @property string $occurred_at
 * @property int|null $created_by
 */
#[Fillable([
    'customer_id',
    'lead_id',
    'type',
    'subject',
    'description',
    'occurred_at',
    'created_by',
])]
class Activity extends Model
{
    use HasFactory, HasTenantIsolation;

    /**
     * Get the customer associated with the activity.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the lead associated with the activity.
     *
     * @return BelongsTo<Lead, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the user who recorded the activity.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the attachments for this activity.
     *
     * @return HasMany<ActivityAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(ActivityAttachment::class);
    }
}
