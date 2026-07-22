<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'work_center_id',
    'code',
    'name',
    'max_capacity_hours',
    'status',
])]
class MfgMachine extends Model
{
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
     * Get downtime history logs.
     *
     * @return HasMany<MfgMachineDowntime, $this>
     */
    public function downtimes(): HasMany
    {
        return $this->hasMany(MfgMachineDowntime::class, 'machine_id');
    }
}
