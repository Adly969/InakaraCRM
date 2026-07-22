<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $entity_type
 * @property int $entity_id
 * @property float $metric_score
 * @property array<string, mixed> $risk_factors
 * @property string|null $summary
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'entity_type',
    'entity_id',
    'metric_score',
    'risk_factors',
    'summary',
])]
class AiEvent extends Model
{
    use HasTenantIsolation, HasUuids;

    protected $casts = [
        'metric_score' => 'decimal:2',
        'risk_factors' => 'array',
    ];
}
