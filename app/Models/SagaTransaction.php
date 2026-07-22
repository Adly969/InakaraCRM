<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SagaTransaction extends Model
{
    protected $fillable = [
        'saga_type',
        'status',
        'correlation_id',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(SagaStep::class, 'saga_transaction_id');
    }
}
