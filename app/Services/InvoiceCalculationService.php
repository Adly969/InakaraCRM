<?php

namespace App\Services;

class InvoiceCalculationService
{
    /**
     * Compute total financial fields for an invoice.
     */
    public function calculate(array $data): array
    {
        $subtotal = 0.00;
        $taxAmount = 0.00;
        $discountAmount = 0.00;

        $items = [];
        foreach ($data['items'] as $item) {
            $qty = (float) $item['quantity'];
            $price = (float) $item['unit_price'];

            $itemSub = $qty * $price;

            // Calculate Item Discounts
            $itemDiscountPercent = (float) ($item['discount_percentage'] ?? 0.00);
            $itemDiscount = round(($itemSub * $itemDiscountPercent) / 100, 2);
            $discountedSub = $itemSub - $itemDiscount;

            // Calculate Item Taxes
            $itemTaxPercent = (float) ($item['tax_percentage'] ?? 11.00); // Default VAT 11%
            $itemTax = round(($discountedSub * $itemTaxPercent) / 100, 2);

            $itemTotal = $discountedSub + $itemTax;

            $subtotal += $itemSub;
            $discountAmount += $itemDiscount;
            $taxAmount += $itemTax;

            $items[] = array_merge($item, [
                'discount_amount' => $itemDiscount,
                'tax_amount' => $itemTax,
                'total_amount' => $itemTotal,
            ]);
        }

        // Sum adjustments
        $adjustmentAmount = 0.00;
        foreach ($data['adjustments'] ?? [] as $adj) {
            $adjustmentAmount += (float) $adj['amount'];
        }

        $totalAmount = max(0.00, ($subtotal - $discountAmount) + $taxAmount + $adjustmentAmount);

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'adjustment_amount' => $adjustmentAmount,
            'total_amount' => $totalAmount,
            'outstanding_balance' => $totalAmount,
            'items' => $items,
        ];
    }
}
