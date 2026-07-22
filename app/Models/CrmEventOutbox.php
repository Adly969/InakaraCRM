<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $tenant_id
 * @property string $event_type
 * @property array $payload
 * @property string|null $processed_at
 */
#[Fillable([
    'tenant_id',
    'event_type',
    'payload',
    'processed_at',
])]
class CrmEventOutbox extends Model
{
    use HasFactory, HasTenantIsolation;

    /**
     * Define custom table name.
     */
    protected $table = 'crm_event_outbox';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'json',
        ];
    }
}
