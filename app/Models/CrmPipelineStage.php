<?php

namespace App\Models;

use App\Casts\ForecastCategoryCast;
use App\Casts\WinProbabilityCast;
use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $pipeline_definition_id
 * @property string $name
 * @property WinProbabilityCast $probability
 * @property int $stage_sequence
 * @property string $forecast_category
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $company_id
 * @property int|null $branch_id
 */
#[Fillable([
    'pipeline_definition_id',
    'name',
    'probability',
    'stage_sequence',
    'forecast_category',
    'is_active',
    'company_id',
    'branch_id',
])]
class CrmPipelineStage extends Model
{
    use HasTenantIsolation;

    /**
     * @var string
     */
    protected $table = 'crm_pipeline_stages';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'probability' => WinProbabilityCast::class,
            'forecast_category' => ForecastCategoryCast::class,
            'stage_sequence' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the pipeline definition this stage belongs to.
     *
     * @return BelongsTo<CrmPipelineDefinition, $this>
     */
    public function pipelineDefinition(): BelongsTo
    {
        return $this->belongsTo(CrmPipelineDefinition::class, 'pipeline_definition_id');
    }
}
