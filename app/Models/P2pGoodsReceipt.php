<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'branch_id',
    'purchase_order_id',
    'receipt_no',
    'received_at',
    'version',
])]
class P2pGoodsReceipt extends Model
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
            'received_at' => 'datetime',
        ];
    }

    /**
     * Get purchase order associated with this goods receipt.
     *
     * @return BelongsTo<P2pPurchaseOrder, $this>
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(P2pPurchaseOrder::class, 'purchase_order_id');
    }

    /**
     * Get items of this goods receipt.
     *
     * @return HasMany<P2pGoodsReceiptItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(P2pGoodsReceiptItem::class, 'goods_receipt_id');
    }
}
