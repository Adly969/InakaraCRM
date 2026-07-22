<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property bool $is_default
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $company_id
 * @property int|null $branch_id
 */
#[Fillable([
    'name',
    'description',
    'is_default',
    'is_active',
    'company_id',
    'branch_id',
])]
class CrmPipelineDefinition extends Model
{
    use HasTenantIsolation;

    /**
     * @var string
     */
    protected $table = 'crm_pipeline_definitions';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the stages for this pipeline.
     *
     * @return HasMany<CrmPipelineStage, $this>
     */
    public function stages(): HasMany
    {
        return $this->hasMany(CrmPipelineStage::class, 'pipeline_definition_id')->orderBy('stage_sequence');
    }
}
