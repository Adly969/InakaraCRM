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
 * @property float $rate
 * @property Carbon $effective_date
 * @property string $rate_provider
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'currency_code',
    'rate',
    'effective_date',
    'rate_provider',
])]
class CurrencyRate extends Model
{
    use HasTenantIsolation, HasUuids;

    protected $casts = [
        'rate' => 'decimal:6',
        'effective_date' => 'date',
    ];
}
