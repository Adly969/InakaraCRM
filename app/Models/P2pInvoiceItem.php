<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'invoice_id',
    'purchase_order_item_id',
    'quantity_invoiced',
    'unit_price_invoiced',
])]
class P2pInvoiceItem extends Model
{
    /**
     * Get invoice document.
     *
     * @return BelongsTo<P2pInvoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(P2pInvoice::class, 'invoice_id');
    }

    /**
     * Get PO line item.
     *
     * @return BelongsTo<P2pPurchaseOrderItem, $this>
     */
    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(P2pPurchaseOrderItem::class, 'purchase_order_item_id');
    }
}
