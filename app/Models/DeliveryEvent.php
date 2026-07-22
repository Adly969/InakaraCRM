<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $delivery_order_id
 * @property int|null $shipment_id
 * @property string $event_type
 * @property array $event_data
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property int $created_by
 * @property Carbon|null $created_at
 */
#[Fillable([
    'delivery_order_id',
    'shipment_id',
    'event_type',
    'event_data',
    'ip_address',
    'user_agent',
    'created_by',
])]
class DeliveryEvent extends Model
{
    use HasFactory;

    /**
     * Disable standard timestamps, only use created_at.
     */
    public $timestamps = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_data' => 'json',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the delivery order this event belongs to.
     *
     * @return BelongsTo<DeliveryOrder, $this>
     */
    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    /**
     * Get the shipment this event is associated with.
     *
     * @return BelongsTo<Shipment, $this>
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * Get the user who triggered this event.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
