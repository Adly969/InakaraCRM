<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $tenant_id
 * @property string $event_id
 * @property int $company_id
 * @property string $event_type
 * @property array $payload
 * @property string $correlation_id
 * @property string $causation_id
 * @property string $trace_id
 * @property string $idempotency_key
 * @property bool $is_dispatched
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'tenant_id',
    'event_id',
    'company_id',
    'event_type',
    'payload',
    'correlation_id',
    'causation_id',
    'trace_id',
    'idempotency_key',
    'is_dispatched',
])]
class SalesEventOutbox extends Model
{
    use HasTenantIsolation;

    protected $table = 'sales_event_outbox';

    protected $casts = [
        'payload' => 'array',
        'is_dispatched' => 'boolean',
    ];
}
