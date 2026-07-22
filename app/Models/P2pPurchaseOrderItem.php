<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'purchase_order_id',
    'sku',
    'quantity_ordered',
    'quantity_received',
    'quantity_invoiced',
    'unit_price',
    'tax_percentage',
    'discount_amount',
])]
class P2pPurchaseOrderItem extends Model
{
    /**
     * Get PO this item belongs to.
     *
     * @return BelongsTo<P2pPurchaseOrder, $this>
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(P2pPurchaseOrder::class, 'purchase_order_id');
    }
}
