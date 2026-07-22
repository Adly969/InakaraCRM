<?php

namespace App\Models;

use App\Enums\StockAdjustmentType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $stock_adjustment_id
 * @property int $inventory_item_id
 * @property StockAdjustmentType $type
 * @property float $quantity_adjusted
 * @property float $unit_cost
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'stock_adjustment_id',
    'inventory_item_id',
    'type',
    'quantity_adjusted',
    'unit_cost',
    'sort_order',
])]
class StockAdjustmentItem extends Model
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
            'type' => StockAdjustmentType::class,
            'quantity_adjusted' => 'decimal:2',
            'unit_cost' => 'decimal:2',
        ];
    }

    /**
     * Get the stock adjustment header for this item.
     */
    public function stockAdjustment(): BelongsTo
    {
        return $this->belongsTo(StockAdjustment::class);
    }

    /**
     * Get the inventory item associated with this line item.
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
