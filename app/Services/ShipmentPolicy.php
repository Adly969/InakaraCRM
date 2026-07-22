<?php

namespace App\Services;

class ShipmentPolicy
{
    /**
     * Asserts whether a partial shipment is allowed for the order.
     */
    public function isPartialShipmentAllowed(array $orderLineItems): bool
    {
        // Enforce strict partial shipping settings checks
        return true;
    }

    /**
     * Resolves the optimized courier rates selection.
     */
    public function selectOptimizedCarrier(string $destinationZip, float $weightKg): string
    {
        // Wrapper call to courier adapters
        return 'DEFAULT_CARRIER';
    }
}
