<?php

namespace App\Services;

use App\Models\InventoryItem;

class CanReserveInventorySpecification
{
    /**
     * Checks if the inventory item has enough unreserved physical stock available.
     */
    public function isSatisfiedBy(InventoryItem $item, float $quantityNeeded): bool
    {
        $availableQty = (float) $item->quantity_current - (float) $item->quantity_reserved;

        return $availableQty >= $quantityNeeded;
    }
}
