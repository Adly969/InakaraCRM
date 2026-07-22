<?php

use App\Enums\StockAdjustmentStatus;
use App\Enums\StockAdjustmentType;
use App\Enums\WarehouseStatus;
use App\Models\InventoryItem;
use App\Models\Warehouse;
use App\Services\CostingService;
use App\Services\InventoryProjectionService;
use App\Services\InventoryTransactionService;
use App\Services\StockAdjustmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it creates and processes a stock adjustment addition and deduction', function () {
    $projService = new InventoryProjectionService;
    $costService = new CostingService;
    $txService = new InventoryTransactionService($projService, $costService);
    $service = new StockAdjustmentService($txService);

    $wh = Warehouse::create([
        'code' => 'WH-001',
        'name' => 'Main Warehouse',
        'type' => 'central',
        'status' => WarehouseStatus::Active,
        'is_default' => true,
    ]);

    $item = InventoryItem::create([
        'warehouse_id' => $wh->id,
        'sku' => 'CHAIR-001',
        'name' => 'Wooden Chair',
        'quantity_current' => 10.00,
        'quantity_reserved' => 0.00,
        'avg_cost_price' => 50000.00,
    ]);

    // Create Draft Stock Adjustment (Deduction of 2 chairs)
    $adj = $service->create([
        'warehouse_id' => $wh->id,
        'adjustment_date' => now()->toDateString(),
        'notes' => 'Damaged inventory adjustment',
        'items' => [
            [
                'inventory_item_id' => $item->id,
                'type' => StockAdjustmentType::Deduction->value,
                'quantity_adjusted' => 2.00,
                'unit_cost' => 50000.00,
            ],
        ],
    ]);

    expect($adj->status)->toBe(StockAdjustmentStatus::Draft)
        ->and($adj->items)->toHaveCount(1);

    // Approve Stock Adjustment
    $service->approve($adj, 'Approved by Manager Acme');

    expect($adj->refresh()->status)->toBe(StockAdjustmentStatus::Approved)
        ->and($adj->approval_note)->toBe('Approved by Manager Acme');

    // Verify current quantity decreased to 8.00
    expect((float) $item->refresh()->quantity_current)->toBe(8.00);
});
