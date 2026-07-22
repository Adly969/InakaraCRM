<?php

namespace App\Events;

use App\Models\DeliveryOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryCancelled
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public DeliveryOrder $deliveryOrder,
        public ?string $reason = null
    ) {}
}
