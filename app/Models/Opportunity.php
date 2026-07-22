<?php

namespace App\Models;

use App\Enums\OpportunityStatus;
use App\Models\Traits\HasTenantIsolation;
use Database\Factories\OpportunityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $lead_id
 * @property int $customer_id
 * @property string $title
 * @property int $pipeline_stage_id
 * @property OpportunityStatus $status
 * @property float $deal_value
 * @property Carbon $expected_close_date
 * @property int|null $loss_reason_id
 * @property string|null $loss_notes
 * @property int $assigned_to
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $company_id
 * @property int|null $branch_id
 */
#[Fillable([
    'lead_id',
    'customer_id',
    'title',
    'deal_value',
    'expected_close_date',
    'loss_reason_id',
    'loss_notes',
    'assigned_to',
    'created_by',
    'updated_by',
    'deleted_by',
    'company_id',
    'branch_id',
])]
class Opportunity extends Model
{
    /** @use HasFactory<OpportunityFactory> */
    use HasFactory, HasTenantIsolation, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'crm_opportunities';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OpportunityStatus::class,
            'deal_value' => 'decimal:2',
            'expected_close_date' => 'date',
            'exchange_rate' => 'decimal:6',
            'base_currency_amount' => 'decimal:2',
            'transaction_currency_amount' => 'decimal:2',
        ];
    }

    /**
     * Get the lead this opportunity was converted from.
     *
     * @return BelongsTo<Lead, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the customer for this opportunity.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the current pipeline stage.
     *
     * @return BelongsTo<CrmPipelineStage, $this>
     */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(CrmPipelineStage::class, 'pipeline_stage_id');
    }

    /**
     * Get the loss reason if lost.
     *
     * @return BelongsTo<CrmLossReason, $this>
     */
    public function lossReason(): BelongsTo
    {
        return $this->belongsTo(CrmLossReason::class, 'loss_reason_id');
    }

    /**
     * Get the assigned sales user.
     *
     * @return BelongsTo<User, $this>
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created this opportunity.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the competitors linked to this opportunity.
     *
     * @return HasMany<CrmOpportunityCompetitor, $this>
     */
    public function competitors(): HasMany
    {
        return $this->hasMany(CrmOpportunityCompetitor::class, 'opportunity_id');
    }

    /**
     * Get the stage transition history.
     *
     * @return HasMany<CrmStageHistory, $this>
     */
    public function stageHistories(): HasMany
    {
        return $this->hasMany(CrmStageHistory::class, 'opportunity_id');
    }

    /**
     * Get the activities for this opportunity.
     *
     * @return HasMany<CrmActivity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(CrmActivity::class, 'opportunity_id');
    }

    /**
     * Calculate expected revenue based on deal value and stage probability.
     */
    public function getExpectedRevenueAttribute(): float
    {
        $probability = 0;
        if ($this->stage && $this->stage->probability) {
            $probability = $this->stage->probability->value;
        }

        return round((float) $this->deal_value * ($probability / 100), 2);
    }
}
