<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $sales_order_id
 * @property int $inventory_item_id
 * @property float $quantity_reserved
 * @property float $quantity_released
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'sales_order_id',
    'inventory_item_id',
    'quantity_reserved',
    'quantity_released',
    'status',
])]
class InventoryReservation extends Model
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
            'quantity_reserved' => 'decimal:2',
            'quantity_released' => 'decimal:2',
        ];
    }

    /**
     * Get the sales order associated with the reservation.
     */
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    /**
     * Get the inventory item associated with the reservation.
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
