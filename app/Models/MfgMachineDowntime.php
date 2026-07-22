<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'machine_id',
    'start_time',
    'end_time',
    'reason_code',
])]
class MfgMachineDowntime extends Model
{
    /**
     * Get attributes casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    /**
     * Get machine.
     *
     * @return BelongsTo<MfgMachine, $this>
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(MfgMachine::class, 'machine_id');
    }
}
