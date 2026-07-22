<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'goods_receipt_id',
    'purchase_order_item_id',
    'sku',
    'quantity_received',
    'quantity_accepted',
    'quantity_rejected',
    'status',
])]
class P2pGoodsReceiptItem extends Model
{
    /**
     * Get goods receipt document.
     *
     * @return BelongsTo<P2pGoodsReceipt, $this>
     */
    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(P2pGoodsReceipt::class, 'goods_receipt_id');
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
