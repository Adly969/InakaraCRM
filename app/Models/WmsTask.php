<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'branch_id',
    'type',
    'status',
    'priority',
    'assigned_to',
    'source_location_id',
    'target_location_id',
    'sku',
    'quantity',
    'batch_number',
    'serial_number',
    'version',
])]
class WmsTask extends Model
{
    use HasTenantIsolation;

    /**
     * Get the source location of the task.
     *
     * @return BelongsTo<WmsLocation, $this>
     */
    public function sourceLocation(): BelongsTo
    {
        return $this->belongsTo(WmsLocation::class, 'source_location_id');
    }

    /**
     * Get the target location of the task.
     *
     * @return BelongsTo<WmsLocation, $this>
     */
    public function targetLocation(): BelongsTo
    {
        return $this->belongsTo(WmsLocation::class, 'target_location_id');
    }

    /**
     * Get the assigned user.
     *
     * @return BelongsTo<User, $this>
     */
    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
