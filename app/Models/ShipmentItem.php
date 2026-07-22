<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $shipment_id
 * @property int $delivery_order_item_id
 * @property float $quantity_shipped
 */
#[Fillable([
    'shipment_id',
    'delivery_order_item_id',
    'quantity_shipped',
])]
class ShipmentItem extends Model
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
            'quantity_shipped' => 'decimal:2',
        ];
    }

    /**
     * Get the shipment this item belongs to.
     *
     * @return BelongsTo<Shipment, $this>
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * Get the delivery order item referenced.
     *
     * @return BelongsTo<DeliveryOrderItem, $this>
     */
    public function deliveryOrderItem(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrderItem::class);
    }
}
