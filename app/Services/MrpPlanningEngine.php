<?php

namespace App\Services;

use App\Models\MfgApsSupplyPegging;
use App\Models\MfgBillOfMaterial;
use App\Models\MfgDemandForecast;
use App\Models\P2pRequisition;
use App\Models\P2pRequisitionItem;
use Illuminate\Support\Facades\DB;

class MrpPlanningEngine
{
    /**
     * Executes the MRP requirements planning run for active SKU forecasts.
     */
    public function runMrp(int $companyId, int $branchId): array
    {
        return DB::transaction(function () use ($companyId, $branchId) {
            $forecasts = MfgDemandForecast::where('company_id', $companyId)->get();
            $recommendations = [];

            foreach ($forecasts as $forecast) {
                $sku = $forecast->sku;
                $demandQty = (float) $forecast->quantity_forecast;

                // Find active BOM for material explosion
                $bom = MfgBillOfMaterial::with('items')
                    ->where('company_id', $companyId)
                    ->where('sku', $sku)
                    ->where('status', 'ACTIVE')
                    ->first();

                if (! $bom) {
                    continue;
                }

                // MRP material explosion: generate purchase recommendations for components
                foreach ($bom->items as $component) {
                    $requiredQty = $demandQty * (float) $component->quantity;

                    // Peg demand details to supply allocation tracking
                    $pegging = MfgApsSupplyPegging::create([
                        'demand_source_type' => 'FORECAST',
                        'demand_source_id' => $forecast->id,
                        'supply_type' => 'PURCHASE_REQS',
                        'supply_id' => 0, // Placeholder populated below after model persist
                        'pegged_quantity' => $requiredQty,
                    ]);

                    // Generate a draft purchase requisition for shortages
                    $pr = P2pRequisition::create([
                        'company_id' => $companyId,
                        'branch_id' => $branchId,
                        'requisition_no' => 'PR-MRP-'.uniqid(),
                        'requester_id' => 1,
                        'cost_center_code' => 'MFG-DEP',
                        'type' => 'OPEX',
                        'status' => 'DRAFT',
                        'total_amount' => 0.00,
                    ]);

                    P2pRequisitionItem::create([
                        'requisition_id' => $pr->id,
                        'sku' => $component->sku,
                        'quantity' => $requiredQty,
                        'unit_price_estimate' => 10.00, // Standard component price estimate
                    ]);

                    $pr->total_amount = $requiredQty * 10.00;
                    $pr->save();

                    // Complete pegging trace connection link
                    $pegging->supply_id = $pr->id;
                    $pegging->save();

                    $recommendations[] = [
                        'sku' => $component->sku,
                        'quantity' => $requiredQty,
                        'pr_no' => $pr->requisition_no,
                    ];
                }
            }

            return $recommendations;
        });
    }
}
