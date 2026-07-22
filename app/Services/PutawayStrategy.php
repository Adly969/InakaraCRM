<?php

namespace App\Services;

use App\Models\WmsLocation;
use InvalidArgumentException;

class PutawayStrategy
{
    /**
     * Resolves the best target bin for putaway based on weight and volume capacities.
     */
    public function resolveTargetBin(int $warehouseId, float $itemWeight, float $itemVolume): WmsLocation
    {
        if ($itemWeight <= 0 || $itemVolume <= 0) {
            throw new InvalidArgumentException('Item weight and volume must be greater than zero.');
        }

        // Retrieve active bins in the warehouse with sufficient weight and volume capacity
        $weightDiff = (float) $itemWeight;
        $volumeDiff = (float) $itemVolume;

        $bin = WmsLocation::where('warehouse_id', $warehouseId)
            ->where('type', 'BIN')
            ->where('status', 'ACTIVE')
            ->whereRaw("(max_weight - current_weight) >= {$weightDiff}")
            ->whereRaw("(max_volume - current_volume) >= {$volumeDiff}")
            ->orderByRaw('(max_weight - current_weight) ASC')
            ->first();

        if (! $bin) {
            throw new InvalidArgumentException('No active bin location found with sufficient weight and volume capacity.');
        }

        return $bin;
    }
}
