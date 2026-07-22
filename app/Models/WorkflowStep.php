<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $workflow_definition_id
 * @property int $step_number
 * @property string $approver_role
 * @property int|null $timeout_hours
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'workflow_definition_id',
    'step_number',
    'approver_role',
    'timeout_hours',
])]
class WorkflowStep extends Model
{
    use HasUuids;

    /**
     * Get the parent workflow definition.
     *
     * @return BelongsTo<WorkflowDefinition, $this>
     */
    public function definition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id');
    }

    /**
     * Get the routing conditions on this step.
     *
     * @return HasMany<WorkflowCondition, $this>
     */
    public function conditions(): HasMany
    {
        return $this->hasMany(WorkflowCondition::class);
    }
}
