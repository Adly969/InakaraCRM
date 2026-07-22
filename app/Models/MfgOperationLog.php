<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'operation_id',
    'user_id',
    'machine_id',
    'quantity_yield',
    'quantity_scrap',
    'labor_hours',
    'machine_hours',
    'logged_at',
])]
class MfgOperationLog extends Model
{
    /**
     * Get attributes casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'logged_at' => 'datetime',
        ];
    }

    /**
     * Get parent operation step.
     *
     * @return BelongsTo<MfgOperation, $this>
     */
    public function operation(): BelongsTo
    {
        return $this->belongsTo(MfgOperation::class, 'operation_id');
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
