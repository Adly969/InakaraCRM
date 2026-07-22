<?php

namespace App\Services\WMS;

use App\Models\InventoryBalance;
use App\Models\InventoryCostLayer;
use App\Models\InventoryTransaction;

class InventoryTransactionService
{
    /**
     * Process Goods Receipt: Increments stock balance & creates FIFO cost layer.
     */
    public function processGoodsReceipt(InventoryTransaction $transaction): void
    {
        foreach ($transaction->items as $item) {
            $balance = InventoryBalance::firstOrCreate(
                [
                    'tenant_id' => $transaction->tenant_id,
                    'warehouse_id' => $transaction->target_warehouse_id,
                    'bin_id' => $item->to_bin_id,
                    'product_id' => $item->product_id,
                    'batch_number' => $item->batch_number,
                ],
                [
                    'company_id' => $transaction->company_id,
                    'quantity_on_hand' => 0,
                    'quantity_reserved' => 0,
                    'quantity_available' => 0,
                    'quantity_quarantine' => 0,
                ]
            );

            $balance->quantity_on_hand += $item->quantity;
            $balance->quantity_available += $item->quantity;
            $balance->save();

            // Create FIFO Cost Layer
            InventoryCostLayer::create([
                'tenant_id' => $transaction->tenant_id,
                'warehouse_id' => $transaction->target_warehouse_id,
                'product_id' => $item->product_id,
                'received_date' => now(),
                'original_quantity' => $item->quantity,
                'remaining_quantity' => $item->quantity,
                'unit_cost' => $item->unit_cost,
            ]);
        }

        $transaction->update(['status' => 'posted']);
    }

    /**
     * Process Goods Issue: Decrements stock balance & consumes FIFO layers.
     */
    public function processGoodsIssue(InventoryTransaction $transaction): void
    {
        foreach ($transaction->items as $item) {
            $balance = InventoryBalance::where('tenant_id', $transaction->tenant_id)
                ->where('warehouse_id', $transaction->source_warehouse_id)
                ->where('product_id', $item->product_id)
                ->first();

            if ($balance && $balance->quantity_available >= $item->quantity) {
                $balance->quantity_on_hand -= $item->quantity;
                $balance->quantity_available -= $item->quantity;
                $balance->save();

                // Consume FIFO Cost Layers
                $qtyToConsume = $item->quantity;
                $layers = InventoryCostLayer::where('tenant_id', $transaction->tenant_id)
                    ->where('warehouse_id', $transaction->source_warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->where('remaining_quantity', '>', 0)
                    ->orderBy('received_date', 'asc')
                    ->get();

                foreach ($layers as $layer) {
                    if ($qtyToConsume <= 0) {
                        break;
                    }
                    $taken = min($layer->remaining_quantity, $qtyToConsume);
                    $layer->remaining_quantity -= $taken;
                    $layer->save();
                    $qtyToConsume -= $taken;
                }
            }
        }

        $transaction->update(['status' => 'posted']);
    }
}
