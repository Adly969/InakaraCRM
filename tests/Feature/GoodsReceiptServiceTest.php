<?php

use App\Enums\GoodsReceiptStatus;
use App\Enums\ProductionOrderStatus;
use App\Enums\ProductionPriority;
use App\Enums\WarehouseStatus;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use App\Models\Warehouse;
use App\Services\CostingService;
use App\Services\GoodsReceiptService;
use App\Services\InventoryProjectionService;
use App\Services\InventoryTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it creates goods receipt from PO and posts to inventory on receive', function () {
    $projService = new InventoryProjectionService;
    $costService = new CostingService;
    $txService = new InventoryTransactionService($projService, $costService);
    $service = new GoodsReceiptService($txService);

    $wh = Warehouse::create([
        'code' => 'WH-001',
        'name' => 'Main Warehouse',
        'type' => 'central',
        'status' => WarehouseStatus::Active,
        'is_default' => true,
    ]);

    $po = ProductionOrder::create([
        'reference_no' => 'PO-001',
        'customer_id' => Customer::factory()->create()->id,
        'subject' => 'Production Batch 1',
        'status' => ProductionOrderStatus::Completed,
        'priority' => ProductionPriority::Normal,
        'target_completion_date' => now()->toDateString(),
        'currency' => 'IDR',
        'total_amount' => 500000,
    ]);

    $poItem = ProductionOrderItem::create([
        'production_order_id' => $po->id,
        'description' => 'Wooden Desk',
        'quantity' => 5.00,
        'unit' => 'pcs',
        'unit_price' => 100000.00,
        'total_price' => 500000.00,
        'sort_order' => 0,
    ]);

    // Create GR Draft
    $gr = $service->createFromProductionOrder($po, $wh);

    expect($gr->status)->toBe(GoodsReceiptStatus::Draft)
        ->and($gr->items)->toHaveCount(1);

    $grItem = $gr->items->first();
    expect($grItem->sku)->toBe('WOODEN-DESK')
        ->and((float) $grItem->quantity_received)->toBe(5.00);

    // Confirm receipt / Post
    $service->receive($gr, 'Received in full without damage');

    expect($gr->refresh()->status)->toBe(GoodsReceiptStatus::Received)
        ->and($gr->remark)->toBe('Received in full without damage');

    // Verify inventory current quantity increased
    $invItem = InventoryItem::where('warehouse_id', $wh->id)
        ->where('sku', 'WOODEN-DESK')
        ->first();

    expect($invItem)->not->toBeNull()
        ->and((float) $invItem->quantity_current)->toBe(5.00)
        ->and((float) $invItem->avg_cost_price)->toBe(100000.00);
});
