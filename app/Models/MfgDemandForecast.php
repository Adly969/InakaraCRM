<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id',
    'sku',
    'forecast_date',
    'quantity_forecast',
])]
class MfgDemandForecast extends Model
{
    use HasTenantIsolation, SoftDeletes;

    /**
     * Get attributes casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'forecast_date' => 'date',
        ];
    }
}
