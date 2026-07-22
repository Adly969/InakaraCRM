<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'production_order_id',
    'material_cost_actual',
    'labor_cost_actual',
    'machine_cost_actual',
    'overhead_cost_actual',
    'variance_amount',
])]
class MfgProductionCost extends Model
{
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
