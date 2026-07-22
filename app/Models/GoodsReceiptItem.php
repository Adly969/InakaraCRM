<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $goods_receipt_id
 * @property int|null $production_order_item_id
 * @property string $sku
 * @property string $description
 * @property float $quantity_received
 * @property string $unit
 * @property float $unit_cost
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'goods_receipt_id',
    'production_order_item_id',
    'sku',
    'description',
    'quantity_received',
    'unit',
    'unit_cost',
    'sort_order',
])]
class GoodsReceiptItem extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity_received' => 'decimal:2',
            'unit_cost' => 'decimal:2',
        ];
    }

    /**
     * Get the goods receipt header for this item.
     */
    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    /**
     * Get the source production order line item for this item.
     */
    public function productionOrderItem(): BelongsTo
    {
        return $this->belongsTo(ProductionOrderItem::class);
    }
}
