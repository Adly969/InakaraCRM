<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $production_order_id
 * @property int|null $sales_order_item_id
 * @property string $description
 * @property float $quantity
 * @property string $unit
 * @property float $unit_price
 * @property float $total_price
 * @property int $sort_order
 */
#[Fillable([
    'production_order_id',
    'sales_order_item_id',
    'description',
    'quantity',
    'unit',
    'unit_price',
    'total_price',
    'sort_order',
])]
class ProductionOrderItem extends Model
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
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the production order associated with this item.
     *
     * @return BelongsTo<ProductionOrder, $this>
     */
    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    /**
     * Get the sales order item this item is mapped to.
     *
     * @return BelongsTo<SalesOrderItem, $this>
     */
    public function salesOrderItem(): BelongsTo
    {
        return $this->belongsTo(SalesOrderItem::class);
    }
}
