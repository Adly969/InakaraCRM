<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryTransactionService
{
    public function __construct(
        protected InventoryProjectionService $projectionService,
        protected CostingService $costingService
    ) {}

    /**
     * Centralized method to post a stock movement transaction.
     */
    public function adjustStock(
        Warehouse $wh,
        InventoryItem $item,
        float $qtyChange,
        string $transactionType, // Enum value
        string $refType,
        int $refId,
        float $costPrice = 0.00,
        ?string $notes = null
    ): InventoryTransaction {
        return DB::transaction(function () use ($wh, $item, $qtyChange, $transactionType, $refType, $refId, $costPrice, $notes) {
            // Pessimistic Locking
            $item->lockForUpdate();

            $qtyBefore = (float) $item->quantity_current;
            $reservedBefore = (float) $item->quantity_reserved;

            $qtyAfter = $qtyBefore + $qtyChange;
            $reservedAfter = $reservedBefore; // Default reservation doesn't change unless released

            // Check negative stock constraint
            if ($qtyAfter < 0) {
                throw ValidationException::withMessages([
                    'stock' => ["Insufficient stock for SKU: {$item->sku} in Warehouse: {$wh->name}."],
                ]);
            }

            // Calculate cost using CostingService if it's a receipt
            $avgCostAfter = (float) $item->avg_cost_price;
            if ($qtyChange > 0 && $costPrice > 0) {
                $avgCostAfter = $this->costingService->calculateMovingAverage($item, $qtyChange, $costPrice);
            }

            $movementDirection = $qtyChange > 0 ? 'in' : ($qtyChange < 0 ? 'out' : 'none');

            // Write Ledger Entry
            $tx = InventoryTransaction::create([
                'inventory_item_id' => $item->id,
                'warehouse_id' => $wh->id,
                'transaction_type' => $transactionType,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'movement_direction' => $movementDirection,
                'quantity_before' => $qtyBefore,
                'quantity_change' => $qtyChange,
                'quantity_after' => $qtyAfter,
                'reserved_before' => $reservedBefore,
                'reserved_after' => $reservedAfter,
                'cost_price' => $costPrice,
                'total_value_change' => $qtyChange * $costPrice,
                'current_avg_cost_after' => $avgCostAfter,
                'notes' => $notes,
                'created_by' => Auth::id(),
            ]);

            // Update projection cache and cost
            $item->update([
                'quantity_current' => $qtyAfter,
                'avg_cost_price' => $avgCostAfter,
            ]);

            return $tx;
        }, 3);
    }
}
