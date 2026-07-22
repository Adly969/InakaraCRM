<?php

namespace App\Events;

use App\Models\ProductionOrder;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductionCompleted
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public ProductionOrder $productionOrder,
        public User $actor
    ) {}
}
