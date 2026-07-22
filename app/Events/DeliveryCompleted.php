<?php

namespace App\Events;

use App\Models\DeliveryOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryCompleted
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public DeliveryOrder $deliveryOrder) {}
}
