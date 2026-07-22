<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'requisition_id',
    'sku',
    'quantity',
    'unit_price_estimate',
])]
class P2pRequisitionItem extends Model
{
    /**
     * Get requisition this line belongs to.
     *
     * @return BelongsTo<P2pRequisition, $this>
     */
    public function requisition(): BelongsTo
    {
        return $this->belongsTo(P2pRequisition::class, 'requisition_id');
    }
}
