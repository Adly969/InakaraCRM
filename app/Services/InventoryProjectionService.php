<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryReservation;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;

class InventoryProjectionService
{
    /**
     * Update projection on inventory item based on a ledger mutator.
     */
    public function updateProjection(InventoryItem $item, float $newQty, float $newReserved): void
    {
        $item->update([
            'quantity_current' => $newQty,
            'quantity_reserved' => $newReserved,
        ]);
    }

    /**
     * Rebuild the projections of current stock and reserved quantities from the ledger source of truth.
     */
    public function rebuildProjectionFromLedger(InventoryItem $item): void
    {
        DB::transaction(function () use ($item) {
            // Re-lock to avoid concurrent updates during rebuild
            $item->lockForUpdate();

            // Sum physical qty changes from the immutable ledger
            $quantityCurrent = (float) InventoryTransaction::where('inventory_item_id', $item->id)
                ->sum('quantity_change');

            // Sum active reservations
            $quantityReserved = (float) InventoryReservation::where('inventory_item_id', $item->id)
                ->where('status', 'active')
                ->sum(DB::raw('quantity_reserved - quantity_released'));

            $item->update([
                'quantity_current' => $quantityCurrent,
                'quantity_reserved' => $quantityReserved,
            ]);
        });
    }
}
