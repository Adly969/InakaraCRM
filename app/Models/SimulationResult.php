<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimulationResult extends Model
{
    protected $fillable = [
        'event_type',
        'payload',
        'simulated_journal',
        'run_by',
    ];

    protected $casts = [
        'payload' => 'json',
        'simulated_journal' => 'json',
    ];

    public function runner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'run_by');
    }
}
