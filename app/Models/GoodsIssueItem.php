<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $goods_issue_id
 * @property int|null $sales_order_item_id
 * @property string $sku
 * @property string $description
 * @property float $quantity_issued
 * @property string $unit
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'goods_issue_id',
    'sales_order_item_id',
    'sku',
    'description',
    'quantity_issued',
    'unit',
    'sort_order',
])]
class GoodsIssueItem extends Model
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
            'quantity_issued' => 'decimal:2',
        ];
    }

    /**
     * Get the goods issue header for this item.
     */
    public function goodsIssue(): BelongsTo
    {
        return $this->belongsTo(GoodsIssue::class);
    }

    /**
     * Get the source sales order line item for this item.
     */
    public function salesOrderItem(): BelongsTo
    {
        return $this->belongsTo(SalesOrderItem::class);
    }
}
