<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $workflow_step_id
 * @property string $field
 * @property string $operator
 * @property string $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'workflow_step_id',
    'field',
    'operator',
    'value',
])]
class WorkflowCondition extends Model
{
    use HasUuids;

    /**
     * Get the parent step.
     *
     * @return BelongsTo<WorkflowStep, $this>
     */
    public function step(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_id');
    }
}
