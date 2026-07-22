<?php

use App\Models\MfgBillOfMaterial;
use App\Models\MfgBomItem;
use App\Models\MfgDemandForecast;
use App\Models\MfgOperation;
use App\Models\MfgProductionOrder;
use App\Models\MfgStandardCost;
use App\Models\MfgWorkCenter;
use App\Models\SalesEventOutbox;
use App\Services\MfgCostEngine;
use App\Services\MrpPlanningEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it runs MRP requirements explosion and pegging links', function () {
    // 1. Create a BOM for SKU 'CAR-WHEEL'
    $bom = MfgBillOfMaterial::create([
        'company_id' => 1,
        'branch_id' => 1,
        'bom_no' => 'BOM-CAR-WHEEL',
        'sku' => 'CAR-WHEEL',
        'status' => 'ACTIVE',
    ]);

    // Components
    MfgBomItem::create([
        'bom_id' => $bom->id,
        'sku' => 'STEEL-RIM',
        'quantity' => 1,
    ]);

    MfgBomItem::create([
        'bom_id' => $bom->id,
        'sku' => 'RUBBER-TYRE',
        'quantity' => 2,
    ]);

    // 2. Create demand forecast
    MfgDemandForecast::create([
        'company_id' => 1,
        'sku' => 'CAR-WHEEL',
        'forecast_date' => now()->addDays(30),
        'quantity_forecast' => 10,
    ]);

    $engine = new MrpPlanningEngine;
    $recommendations = $engine->runMrp(1, 1);

    expect($recommendations)->toHaveCount(2)
        ->and($recommendations[0]['sku'])->toBe('STEEL-RIM')
        ->and((float) $recommendations[0]['quantity'])->toEqual(10)
        ->and($recommendations[1]['sku'])->toBe('RUBBER-TYRE')
        ->and((float) $recommendations[1]['quantity'])->toEqual(20);
});

test('it calculates actual WIP costing overhead and logs costing outbox entries', function () {
    // 1. Create standard costing baseline
    MfgStandardCost::create([
        'company_id' => 1,
        'sku' => 'CAR-ENGINE',
        'standard_material_cost' => 5000.00,
        'standard_labor_cost' => 100.00,
        'standard_machine_cost' => 150.00,
        'is_active' => true,
    ]);

    // 2. Create Work Center
    $wc = MfgWorkCenter::create([
        'company_id' => 1,
        'branch_id' => 1,
        'code' => 'WC-ENG',
        'name' => 'Engine Assembly Station',
        'hourly_labor_rate' => 50.00,
        'hourly_machine_rate' => 120.00,
    ]);

    // 3. Create Production Order
    $order = MfgProductionOrder::create([
        'company_id' => 1,
        'branch_id' => 1,
        'production_no' => 'PO-ENG-001',
        'sku' => 'CAR-ENGINE',
        'quantity_planned' => 5,
        'quantity_produced' => 4, // 1 scrap short
        'quantity_scrapped' => 1,
        'status' => 'STARTED',
    ]);

    // 4. Create actual operation step
    MfgOperation::create([
        'production_order_id' => $order->id,
        'step_sequence' => 10,
        'work_center_id' => $wc->id,
        'status' => 'COMPLETED',
        'labor_hours_logged' => 8.00,  // Cost = 8 * 50 = 400.00
        'machine_hours_logged' => 6.00, // Cost = 6 * 120 = 720.00
    ]);

    $costEngine = new MfgCostEngine;
    $cost = $costEngine->calculateWipCosts($order->id);

    // Actual material cost = 4 produced * 5000 standard material cost = 20,000.00
    // Direct Labor = 400.00
    // Machine = 720.00
    // Overhead = (400 + 720) * 0.15 = 168.00
    // Total Actual = 21,288.00
    // Total Planned = 5 planned * 5000 = 25,000.00
    // Variance = 21,288.00 - 25,000.00 = -3,712.00
    expect((float) $cost->material_cost_actual)->toEqual(20000.00)
        ->and((float) $cost->labor_cost_actual)->toEqual(400.00)
        ->and((float) $cost->machine_cost_actual)->toEqual(720.00)
        ->and((float) $cost->overhead_cost_actual)->toEqual(168.00)
        ->and((float) $cost->variance_amount)->toEqual(-3712.00);

    // Verify outbox entry is logged for accounting publication SAGA
    expect(SalesEventOutbox::count())->toBe(1);
});
