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
 * @property string $rule_type
 * @property array<string, mixed> $conditions
 * @property array<string, mixed> $actions
 * @property Carbon $effective_date
 * @property Carbon $expiration_date
 * @property int $priority
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'rule_type',
    'conditions',
    'actions',
    'effective_date',
    'expiration_date',
    'priority',
])]
class BusinessRule extends Model
{
    use HasTenantIsolation, HasUuids;

    protected $casts = [
        'conditions' => 'array',
        'actions' => 'array',
        'effective_date' => 'date',
        'expiration_date' => 'date',
    ];
}
