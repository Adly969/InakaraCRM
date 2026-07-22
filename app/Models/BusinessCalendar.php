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
 * @property int $fiscal_year
 * @property Carbon $period_start
 * @property Carbon $period_end
 * @property array<int, string> $holidays
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'fiscal_year',
    'period_start',
    'period_end',
    'holidays',
    'status',
])]
class BusinessCalendar extends Model
{
    use HasTenantIsolation, HasUuids;

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'holidays' => 'array',
    ];
}
