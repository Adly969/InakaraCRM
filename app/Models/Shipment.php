<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $delivery_order_id
 * @property string $reference_no
 * @property int|null $carrier_id
 * @property int|null $driver_id
 * @property string $courier_type
 * @property string|null $tracking_number
 * @property string $status
 * @property float $estimated_cost
 * @property float $actual_cost
 * @property string $currency
 * @property float $exchange_rate
 * @property Carbon|null $estimated_delivery_date
 * @property Carbon|null $actual_delivery_date
 * @property int $created_by
 * @property int|null $updated_by
 */
#[Fillable([
    'delivery_order_id',
    'reference_no',
    'carrier_id',
    'driver_id',
    'courier_type',
    'tracking_number',
    'status',
    'estimated_cost',
    'actual_cost',
    'currency',
    'exchange_rate',
    'estimated_delivery_date',
    'actual_delivery_date',
    'created_by',
    'updated_by',
])]
class Shipment extends Model
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
            'estimated_cost' => 'decimal:2',
            'actual_cost' => 'decimal:2',
            'exchange_rate' => 'decimal:6',
            'estimated_delivery_date' => 'datetime:Y-m-d',
            'actual_delivery_date' => 'datetime:Y-m-d',
        ];
    }

    /**
     * Get the delivery order associated with this shipment.
     *
     * @return BelongsTo<DeliveryOrder, $this>
     */
    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    /**
     * Get the carrier associated with this shipment.
     *
     * @return BelongsTo<Carrier, $this>
     */
    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    /**
     * Get the driver associated with this shipment.
     *
     * @return BelongsTo<Driver, $this>
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the items in this shipment.
     *
     * @return HasMany<ShipmentItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }
}
