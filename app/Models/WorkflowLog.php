<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $workflow_step_id
 * @property int $document_id
 * @property string $action
 * @property int $actor_id
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'workflow_step_id',
    'document_id',
    'action',
    'actor_id',
    'notes',
])]
class WorkflowLog extends Model
{
    use HasTenantIsolation, HasUuids;

    /**
     * Get the workflow step linked to this log.
     *
     * @return BelongsTo<WorkflowStep, $this>
     */
    public function step(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_id');
    }

    /**
     * Get the user who left the log.
     *
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
