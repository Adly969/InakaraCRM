<?php

use App\Enums\WarehouseStatus;
use App\Models\InventoryItem;
use App\Models\Warehouse;
use App\Services\CostingService;
use App\Services\InventoryProjectionService;
use App\Services\InventoryTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('it handles stock mutations and maintains costing and projection state', function () {
    $projService = new InventoryProjectionService;
    $costService = new CostingService;
    $txService = new InventoryTransactionService($projService, $costService);

    $wh = Warehouse::create([
        'code' => 'WH-001',
        'name' => 'Main Warehouse',
        'type' => 'central',
        'is_default' => true,
        'status' => WarehouseStatus::Active,
    ]);

    $item = InventoryItem::create([
        'warehouse_id' => $wh->id,
        'sku' => 'CHAIR-001',
        'name' => 'Wooden Chair',
        'quantity_current' => 0.00,
        'quantity_reserved' => 0.00,
        'avg_cost_price' => 0.00,
    ]);

    // Positive adjustment (Receipt)
    $tx1 = $txService->adjustStock(
        $wh,
        $item,
        10.00,
        'receipt',
        'TestModel',
        1,
        100.00,
        'First Receipt'
    );

    $item->refresh();
    expect((float) $item->quantity_current)->toBe(10.00)
        ->and((float) $item->avg_cost_price)->toBe(100.00);

    $this->assertDatabaseHas('inventory_transactions', [
        'id' => $tx1->id,
        'inventory_item_id' => $item->id,
        'quantity_change' => 10.00,
        'quantity_after' => 10.00,
        'cost_price' => 100.00,
    ]);

    // Second Receipt with new costing
    $tx2 = $txService->adjustStock(
        $wh,
        $item,
        10.00,
        'receipt',
        'TestModel',
        2,
        200.00,
        'Second Receipt'
    );

    $item->refresh();
    // Avg Cost: ((10 * 100) + (10 * 200)) / 20 = 150
    expect((float) $item->quantity_current)->toBe(20.00)
        ->and((float) $item->avg_cost_price)->toBe(150.00);

    // Negative mutation check (Issue)
    $tx3 = $txService->adjustStock(
        $wh,
        $item,
        -5.00,
        'issue',
        'TestModel',
        3,
        0.00,
        'Staging issue'
    );

    $item->refresh();
    expect((float) $item->quantity_current)->toBe(15.00)
        ->and((float) $item->avg_cost_price)->toBe(150.00); // Issue doesn't change average cost!

    // Verify projection rebuilder
    $item->update([
        'quantity_current' => 99.00, // Intentionally corrupt projection cache
    ]);

    $projService->rebuildProjectionFromLedger($item);
    expect((float) $item->refresh()->quantity_current)->toBe(15.00);
});

test('it throws validation exception on insufficient stock', function () {
    $projService = new InventoryProjectionService;
    $costService = new CostingService;
    $txService = new InventoryTransactionService($projService, $costService);

    $wh = Warehouse::create([
        'code' => 'WH-TEST',
        'name' => 'Test WH',
        'type' => 'central',
        'status' => WarehouseStatus::Active,
    ]);

    $item = InventoryItem::create([
        'warehouse_id' => $wh->id,
        'sku' => 'CHAIR-001',
        'name' => 'Wooden Chair',
        'quantity_current' => 2.00,
        'quantity_reserved' => 0.00,
        'avg_cost_price' => 50.00,
    ]);

    $this->expectException(ValidationException::class);

    $txService->adjustStock(
        $wh,
        $item,
        -5.00,
        'issue',
        'TestModel',
        1,
        0.00
    );
});
