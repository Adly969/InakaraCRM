<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SagaStep extends Model
{
    protected $fillable = [
        'saga_transaction_id',
        'step_name',
        'status',
        'payload',
        'compensation_payload',
    ];

    protected $casts = [
        'payload' => 'json',
        'compensation_payload' => 'json',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(SagaTransaction::class, 'saga_transaction_id');
    }
}
