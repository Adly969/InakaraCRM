<?php

namespace App\Services;

class PaymentCalculationService
{
    /**
     * Compute allocated and unallocated totals.
     *
     * @param  array<int, array<string, mixed>>  $allocations
     * @return array{allocated_amount: float, unallocated_amount: float}
     */
    public function calculate(float $paymentAmount, array $allocations): array
    {
        $allocated = 0.00;
        foreach ($allocations as $alloc) {
            $allocated += (float) $alloc['amount'];
        }

        $unallocated = max(0.00, $paymentAmount - $allocated);

        return [
            'allocated_amount' => round($allocated, 2),
            'unallocated_amount' => round($unallocated, 2),
        ];
    }
}
