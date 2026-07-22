<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_id',
    'sku',
    'receipt_date',
    'quantity_initial',
    'quantity_remaining',
    'unit_cost',
    'is_active',
])]
class WmsCostLayer extends Model
{
    use HasTenantIsolation;

    /**
     * Get attributes casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'receipt_date' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
