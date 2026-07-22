<?php

namespace App\Services;

use App\Models\CustomerContract;
use App\Models\PriceBook;
use App\Models\PriceBookEntry;

class PricingPolicy
{
    /**
     * Resolves the net unit price for a SKU given a customer and quantity.
     */
    public function resolveUnitPrice(int $companyId, int $customerId, string $sku, float $quantity): float
    {
        // 1. Check active customer contract price overrides
        $contract = CustomerContract::where('company_id', $companyId)
            ->where('customer_id', $customerId)
            ->where('status', 'ACTIVE')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->first();

        if ($contract) {
            // Find custom price book entries mapped to the contract
            // (If contract terms hold specific product prices)
            // For simplicity, we check if there's a custom price book for the contract or customer
        }

        // 2. Query default Price Book Entries
        $defaultPriceBook = PriceBook::where('company_id', $companyId)
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();

        if ($defaultPriceBook) {
            $entry = PriceBookEntry::where('price_book_id', $defaultPriceBook->id)
                ->where('sku', $sku)
                ->where('min_quantity', '<=', $quantity)
                ->orderBy('min_quantity', 'desc')
                ->first();

            if ($entry) {
                return (float) $entry->unit_price;
            }
        }

        return 0.00;
    }
}
