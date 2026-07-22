<?php

namespace App\Services;

class DiscountPolicy
{
    /**
     * Verifies if the proposed discount rate is valid and safe.
     */
    public function validateDiscount(float $basePrice, float $discountAmount, float $cost = 0.00): bool
    {
        if ($discountAmount < 0) {
            return false;
        }

        $netPrice = $basePrice - $discountAmount;

        // Discount cannot drop margins below cost limit bounds (default: 10% safety margin)
        if ($cost > 0 && $netPrice < ($cost * 1.10)) {
            return false;
        }

        // Maximum allowable discount is 70% of base price
        if ($discountAmount > ($basePrice * 0.70)) {
            return false;
        }

        return true;
    }
}
