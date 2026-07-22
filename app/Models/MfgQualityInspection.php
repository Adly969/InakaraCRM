<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'branch_id',
    'production_order_id',
    'inspector_id',
    'quantity_tested',
    'quantity_passed',
    'quantity_failed',
    'status',
    'inspection_notes',
])]
class MfgQualityInspection extends Model
{
    use HasTenantIsolation;

    /**
     * Get production order.
     *
     * @return BelongsTo<MfgProductionOrder, $this>
     */
    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(MfgProductionOrder::class, 'production_order_id');
    }
}
