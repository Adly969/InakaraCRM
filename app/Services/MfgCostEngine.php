<?php

namespace App\Services;

use App\Models\MfgOperation;
use App\Models\MfgProductionCost;
use App\Models\MfgProductionOrder;
use App\Models\MfgStandardCost;
use App\Models\MfgWipLedger;
use App\Models\SalesEventOutbox;
use Illuminate\Support\Facades\DB;

class MfgCostEngine
{
    /**
     * Calculates actual manufacturing costs and records variances against standard baselines.
     */
    public function calculateWipCosts(int $productionOrderId): MfgProductionCost
    {
        return DB::transaction(function () use ($productionOrderId) {
            $order = MfgProductionOrder::findOrFail($productionOrderId);

            // Accumulate direct labor and machine runtime costs
            $operations = MfgOperation::with('workCenter')->where('production_order_id', $order->id)->get();
            $actualLabor = 0.00;
            $actualMachine = 0.00;

            foreach ($operations as $op) {
                $wc = $op->workCenter;
                if ($wc) {
                    $actualLabor += (float) $op->labor_hours_logged * (float) $wc->hourly_labor_rate;
                    $actualMachine += (float) $op->machine_hours_logged * (float) $wc->hourly_machine_rate;
                }
            }

            // Standard material cost lookups
            $standardCost = MfgStandardCost::where('company_id', $order->company_id)
                ->where('sku', $order->sku)
                ->first();

            $stdPrice = $standardCost ? (float) $standardCost->standard_material_cost : 100.00;
            $actualMaterial = (float) $order->quantity_produced * $stdPrice;
            $overhead = ($actualLabor + $actualMachine) * 0.15; // Standard 15% manufacturing burden overhead rate

            $totalActual = $actualMaterial + $actualLabor + $actualMachine + $overhead;
            $totalPlanned = (float) $order->quantity_planned * $stdPrice;
            $variance = $totalActual - $totalPlanned;

            $cost = MfgProductionCost::updateOrCreate(
                ['production_order_id' => $order->id],
                [
                    'material_cost_actual' => $actualMaterial,
                    'labor_cost_actual' => $actualLabor,
                    'machine_cost_actual' => $actualMachine,
                    'overhead_cost_actual' => $overhead,
                    'variance_amount' => $variance,
                ]
            );

            // Record direct debit to WIP ledger
            MfgWipLedger::create([
                'company_id' => $order->company_id,
                'branch_id' => $order->branch_id,
                'production_order_id' => $order->id,
                'debit_amount' => $totalActual,
                'credit_amount' => 0.00,
                'balance_amount' => $totalActual,
            ]);

            // Decoupled Financial Posting - push WIP cost calculation event to transactional outbox
            SalesEventOutbox::create([
                'event_id' => 'evt-mfg-'.uniqid(),
                'company_id' => $order->company_id,
                'event_type' => 'WipCostCalculated',
                'payload' => [
                    'production_order_id' => $order->id,
                    'production_no' => $order->production_no,
                    'total_actual' => $totalActual,
                    'variance' => $variance,
                    'company_id' => $order->company_id,
                    'branch_id' => $order->branch_id,
                    'schema_version' => 1,
                ],
                'correlation_id' => 'corr-mfg-'.uniqid(),
                'causation_id' => 'caus-mfg-'.uniqid(),
                'trace_id' => 'trace-mfg-'.uniqid(),
                'idempotency_key' => 'idem-mfg-cost-'.$order->id,
                'is_dispatched' => false,
            ]);

            return $cost;
        });
    }
}
