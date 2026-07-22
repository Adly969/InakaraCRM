<?php

namespace App\Services;

use App\Models\InventoryItem;

class CostingService
{
    /**
     * Calculate moving average unit cost.
     * Formula:
     * New Cost = ((Current Qty * Current Avg Cost) + (Received Qty * Received Unit Cost)) / (Current Qty + Received Qty)
     */
    public function calculateMovingAverage(InventoryItem $item, float $receivedQty, float $unitCost): float
    {
        $currentQty = (float) $item->quantity_current;
        $currentCost = (float) $item->avg_cost_price;

        if ($currentQty + $receivedQty <= 0) {
            return $unitCost;
        }

        $totalCurrentVal = $currentQty * $currentCost;
        $totalReceivedVal = $receivedQty * $unitCost;

        return ($totalCurrentVal + $totalReceivedVal) / ($currentQty + $receivedQty);
    }
}
