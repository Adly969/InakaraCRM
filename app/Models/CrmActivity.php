<?php

namespace App\Models;

use App\Enums\CrmActivityStatus;
use App\Enums\CrmActivityType;
use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property CrmActivityType $activity_type
 * @property string $subject
 * @property string|null $description
 * @property Carbon $start_time
 * @property Carbon|null $end_time
 * @property CrmActivityStatus $status
 * @property int|null $lead_id
 * @property int|null $opportunity_id
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $company_id
 * @property int|null $branch_id
 */
#[Fillable([
    'activity_type',
    'subject',
    'description',
    'start_time',
    'end_time',
    'status',
    'lead_id',
    'opportunity_id',
    'created_by',
    'company_id',
    'branch_id',
])]
class CrmActivity extends Model
{
    use HasTenantIsolation;

    /**
     * @var string
     */
    protected $table = 'crm_activities';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activity_type' => CrmActivityType::class,
            'status' => CrmActivityStatus::class,
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    /**
     * Get the lead this activity belongs to.
     *
     * @return BelongsTo<Lead, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the opportunity this activity belongs to.
     *
     * @return BelongsTo<Opportunity, $this>
     */
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    /**
     * Get the user who created this activity.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
