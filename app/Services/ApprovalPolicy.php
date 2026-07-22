<?php

namespace App\Services;

class ApprovalPolicy
{
    /**
     * Resolves the required approval level code for a sales order amount.
     */
    public function resolveRequiredLevel(float $totalAmount, string $currency = 'IDR'): int
    {
        // Default conversions to IDR for limits calculations
        $amountInIdr = $totalAmount;
        if ($currency !== 'IDR') {
            // Converts currency values using current rates
            $amountInIdr = app(ExchangeRateService::class)->convertToBase($totalAmount, $currency);
        }

        if ($amountInIdr <= 15000000.00) {
            return 0; // Auto-approved
        }

        if ($amountInIdr <= 150000000.00) {
            return 1; // Level 1: Supervisor Check
        }

        if ($amountInIdr <= 750000000.00) {
            return 2; // Level 2: Finance Manager
        }

        return 3; // Level 3: VP / Board of Directors
    }
}
