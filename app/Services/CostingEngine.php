<?php

namespace App\Services;

use App\Models\WmsCostLayer;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CostingEngine
{
    /**
     * Records a new inbound costing layer.
     */
    public function recordInboundLayer(int $companyId, string $sku, float $quantity, float $unitCost): WmsCostLayer
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Inbound quantity must be greater than zero.');
        }
        if ($unitCost < 0) {
            throw new InvalidArgumentException('Unit cost cannot be negative.');
        }

        return WmsCostLayer::create([
            'company_id' => $companyId,
            'sku' => $sku,
            'receipt_date' => now(),
            'quantity_initial' => $quantity,
            'quantity_remaining' => $quantity,
            'unit_cost' => $unitCost,
            'is_active' => true,
        ]);
    }

    /**
     * Issues stock using FIFO costing layer queues.
     * Decrements matching cost layers and returns the total cost of issued stock.
     */
    public function issueFifo(int $companyId, string $sku, float $quantityToIssue): float
    {
        if ($quantityToIssue <= 0) {
            throw new InvalidArgumentException('Quantity to issue must be greater than zero.');
        }

        return DB::transaction(function () use ($companyId, $sku, $quantityToIssue) {
            $layers = WmsCostLayer::where('company_id', $companyId)
                ->where('sku', $sku)
                ->where('is_active', true)
                ->where('quantity_remaining', '>', 0)
                ->orderBy('receipt_date', 'asc')
                ->lockForUpdate()
                ->get();

            $totalAvailable = $layers->sum('quantity_remaining');
            if ($totalAvailable < $quantityToIssue) {
                throw new InvalidArgumentException("Insufficient costing layer stock available for SKU {$sku}. Available: {$totalAvailable}, Requested: {$quantityToIssue}.");
            }

            $remainingToIssue = $quantityToIssue;
            $totalCostIssued = 0.00;

            foreach ($layers as $layer) {
                if ($remainingToIssue <= 0) {
                    break;
                }

                $qtyFromLayer = min((float) $layer->quantity_remaining, $remainingToIssue);
                $totalCostIssued += ($qtyFromLayer * (float) $layer->unit_cost);

                $layer->quantity_remaining = (float) $layer->quantity_remaining - $qtyFromLayer;
                $layer->save();

                $remainingToIssue -= $qtyFromLayer;
            }

            return round($totalCostIssued, 2);
        });
    }

    /**
     * Recalculates moving average cost on receipt.
     */
    public function calculateMovingAverage(float $currentQty, float $currentCost, float $inboundQty, float $inboundCost): float
    {
        $totalQty = $currentQty + $inboundQty;
        if ($totalQty <= 0) {
            return 0.00;
        }

        $totalValue = ($currentQty * $currentCost) + ($inboundQty * $inboundCost);

        return round($totalValue / $totalQty, 2);
    }
}
