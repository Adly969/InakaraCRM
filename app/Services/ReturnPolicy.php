<?php

namespace App\Services;

class ReturnPolicy
{
    /**
     * Verifies return conditions and calculates restocking deductions.
     */
    public function calculateRestockingFee(float $returnedValue, string $condition = 'GOOD'): float
    {
        if ($condition === 'DAMAGED') {
            // 25% fee for damaged item conditions
            return $returnedValue * 0.25;
        }

        // 5% standard restocking fee
        return $returnedValue * 0.05;
    }
}
