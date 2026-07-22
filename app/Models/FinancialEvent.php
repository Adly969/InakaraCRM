<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FinancialEvent extends Model
{
    use HasTenantIsolation;

    protected $fillable = [
        'event_uuid',
        'company_id',
        'branch_id',
        'event_type',
        'source_module',
        'payload',
        'status',
        'idempotency_key',
        'correlation_id',
    ];

    protected $casts = [
        'payload' => 'json',
    ];

    public function postingJob(): HasOne
    {
        return $this->hasOne(PostingJob::class, 'event_id');
    }
}
