<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'routing_id',
    'step_sequence',
    'work_center_id',
    'setup_time_minutes',
    'run_time_minutes',
    'description',
])]
class MfgRoutingStep extends Model
{
    /**
     * Get parent routing.
     *
     * @return BelongsTo<MfgRouting, $this>
     */
    public function routing(): BelongsTo
    {
        return $this->belongsTo(MfgRouting::class, 'routing_id');
    }

    /**
     * Get work center where this step is processed.
     *
     * @return BelongsTo<MfgWorkCenter, $this>
     */
    public function workCenter(): BelongsTo
    {
        return $this->belongsTo(MfgWorkCenter::class, 'work_center_id');
    }
}
