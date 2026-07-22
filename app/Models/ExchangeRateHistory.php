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
 * @property string $currency_code
 * @property float $previous_rate
 * @property float $new_rate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'currency_code',
    'previous_rate',
    'new_rate',
])]
class ExchangeRateHistory extends Model
{
    use HasTenantIsolation, HasUuids;

    protected $table = 'exchange_rate_history';

    protected $casts = [
        'previous_rate' => 'decimal:6',
        'new_rate' => 'decimal:6',
    ];
}
