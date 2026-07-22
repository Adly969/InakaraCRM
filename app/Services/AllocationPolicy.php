<?php

namespace App\Services;

class AllocationPolicy
{
    /**
     * Resolves inventory batch allocations using FIFO or FEFO strategy rules.
     *
     * @param  array<int, array<string, mixed>>  $batches
     */
    public function sortBatches(array $batches, string $strategy = 'FIFO'): array
    {
        usort($batches, function ($a, $b) use ($strategy) {
            if ($strategy === 'FEFO') {
                $expiryA = $a['expires_at'] ?? '9999-12-31';
                $expiryB = $b['expires_at'] ?? '9999-12-31';

                return strcmp($expiryA, $expiryB);
            }

            // Default FIFO sorting by created_at or batch_id
            $dateA = $a['created_at'] ?? '0000-00-00';
            $dateB = $b['created_at'] ?? '0000-00-00';

            return strcmp($dateA, $dateB);
        });

        return $batches;
    }
}
