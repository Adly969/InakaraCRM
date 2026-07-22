<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $opportunity_id
 * @property int $from_stage_id
 * @property int $to_stage_id
 * @property int $changed_by
 * @property int $duration_in_seconds
 * @property Carbon $created_at
 * @property int|null $company_id
 * @property int|null $branch_id
 */
#[Fillable([
    'opportunity_id',
    'from_stage_id',
    'to_stage_id',
    'changed_by',
    'duration_in_seconds',
    'company_id',
    'branch_id',
])]
class CrmStageHistory extends Model
{
    use HasTenantIsolation;

    /**
     * @var string
     */
    protected $table = 'crm_stage_histories';

    /**
     * Disable updated_at since this is append-only.
     */
    public const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration_in_seconds' => 'integer',
        ];
    }

    /**
     * Get the opportunity.
     *
     * @return BelongsTo<Opportunity, $this>
     */
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    /**
     * Get the source stage.
     *
     * @return BelongsTo<CrmPipelineStage, $this>
     */
    public function fromStage(): BelongsTo
    {
        return $this->belongsTo(CrmPipelineStage::class, 'from_stage_id');
    }

    /**
     * Get the target stage.
     *
     * @return BelongsTo<CrmPipelineStage, $this>
     */
    public function toStage(): BelongsTo
    {
        return $this->belongsTo(CrmPipelineStage::class, 'to_stage_id');
    }

    /**
     * Get the user who made the change.
     *
     * @return BelongsTo<User, $this>
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
