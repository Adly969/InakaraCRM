<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $delivery_order_id
 * @property int $sales_order_item_id
 * @property string $sku
 * @property string $description
 * @property float $quantity_requested
 * @property float $quantity_shipped
 * @property float $quantity_delivered
 * @property string $unit
 * @property array $item_specifications_snapshot
 * @property int $sort_order
 */
#[Fillable([
    'delivery_order_id',
    'sales_order_item_id',
    'sku',
    'description',
    'quantity_requested',
    'quantity_shipped',
    'quantity_delivered',
    'unit',
    'item_specifications_snapshot',
    'sort_order',
])]
class DeliveryOrderItem extends Model
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
            'quantity_requested' => 'decimal:2',
            'quantity_shipped' => 'decimal:2',
            'quantity_delivered' => 'decimal:2',
            'item_specifications_snapshot' => 'json',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the delivery order that owns this item.
     *
     * @return BelongsTo<DeliveryOrder, $this>
     */
    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class);
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

    /**
     * Get the shipment items containing this item.
     *
     * @return HasMany<ShipmentItem, $this>
     */
    public function shipmentItems(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }
}
