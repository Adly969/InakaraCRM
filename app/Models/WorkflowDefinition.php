<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $name
 * @property string $trigger_event
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'name',
    'trigger_event',
    'status',
])]
class WorkflowDefinition extends Model
{
    use HasTenantIsolation, HasUuids;

    /**
     * Get the steps defined for this workflow.
     *
     * @return HasMany<WorkflowStep, $this>
     */
    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class);
    }
}
