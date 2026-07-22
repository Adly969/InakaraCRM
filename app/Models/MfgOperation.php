<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'production_order_id',
    'step_sequence',
    'work_center_id',
    'status',
    'actual_setup_minutes',
    'actual_run_minutes',
    'labor_hours_logged',
    'machine_hours_logged',
])]
class MfgOperation extends Model
{
    /**
     * Get production order.
     *
     * @return BelongsTo<MfgProductionOrder, $this>
     */
    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(MfgProductionOrder::class, 'production_order_id');
    }

    /**
     * Get work center.
     *
     * @return BelongsTo<MfgWorkCenter, $this>
     */
    public function workCenter(): BelongsTo
    {
        return $this->belongsTo(MfgWorkCenter::class, 'work_center_id');
    }

    /**
     * Get labor/machine logs recorded for this operation.
     *
     * @return HasMany<MfgOperationLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(MfgOperationLog::class, 'operation_id');
    }
}
