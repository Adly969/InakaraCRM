<?php

use App\Enums\GoodsIssueStatus;
use App\Enums\SalesOrderStatus;
use App\Enums\WarehouseStatus;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Warehouse;
use App\Services\CostingService;
use App\Services\GoodsIssueService;
use App\Services\InventoryProjectionService;
use App\Services\InventoryTransactionService;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it creates goods issue from SO, reserves stock, and posts to inventory on issue', function () {
    $projService = new InventoryProjectionService;
    $costService = new CostingService;
    $txService = new InventoryTransactionService($projService, $costService);
    $resService = new ReservationService($projService);
    $service = new GoodsIssueService($txService, $resService);

    $wh = Warehouse::create([
        'code' => 'WH-001',
        'name' => 'Main Warehouse',
        'type' => 'central',
        'status' => WarehouseStatus::Active,
        'is_default' => true,
    ]);

    $so = SalesOrder::create([
        'reference_no' => 'SO-001',
        'customer_id' => Customer::factory()->create()->id,
        'subject' => 'Order Batch 1',
        'status' => SalesOrderStatus::Confirmed,
        'currency' => 'IDR',
        'total_amount' => 500000,
    ]);

    $soItem = SalesOrderItem::create([
        'sales_order_id' => $so->id,
        'sku' => 'DESK-001',
        'description' => 'Wooden Desk',
        'quantity' => 2.00,
        'unit' => 'pcs',
        'unit_price' => 250000.00,
        'total_price' => 500000.00,
    ]);

    // Setup initial stock for WOODEN-DESK so it can be reserved and issued
    $invItem = InventoryItem::create([
        'warehouse_id' => $wh->id,
        'sku' => 'WOODEN-DESK',
        'name' => 'Wooden Desk',
        'quantity_current' => 10.00,
        'quantity_reserved' => 0.00,
        'avg_cost_price' => 200000.00,
    ]);

    // 1. Reserve stock
    $resService->reserveStock($so);

    expect((float) $invItem->refresh()->quantity_reserved)->toBe(2.00)
        ->and((float) $invItem->refresh()->quantity_current)->toBe(10.00);

    // 2. Create Goods Issue Draft
    $gi = $service->createFromSalesOrder($so, $wh);

    expect($gi->status)->toBe(GoodsIssueStatus::Draft)
        ->and($gi->items)->toHaveCount(1);

    // 3. Post Goods Issue
    $service->issue($gi, 'Shipped safely');

    expect($gi->refresh()->status)->toBe(GoodsIssueStatus::Issued)
        ->and($gi->remark)->toBe('Shipped safely');

    // 4. Verify inventory states: Current decreased by 2, Reserved decreased by 2
    $invItem->refresh();
    expect((float) $invItem->quantity_current)->toBe(8.00)
        ->and((float) $invItem->quantity_reserved)->toBe(0.00);
});
