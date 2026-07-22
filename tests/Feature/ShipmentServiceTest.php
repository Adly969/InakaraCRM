<?php

use App\Enums\SalesOrderStatus;
use App\Enums\WarehouseStatus;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\CostingService;
use App\Services\DeliveryConfirmationService;
use App\Services\DeliveryNumberGenerator;
use App\Services\DeliveryOrderService;
use App\Services\DeliveryValidationService;
use App\Services\InventoryProjectionService;
use App\Services\InventoryTransactionService;
use App\Services\ShipmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it creates shipment, updates status to dispatched, and completes delivery', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $numGen = new DeliveryNumberGenerator;
    $valService = new DeliveryValidationService;
    $doService = new DeliveryOrderService($numGen, $valService);
    $shipService = new ShipmentService($numGen);

    $projService = new InventoryProjectionService;
    $costService = new CostingService;
    $txService = new InventoryTransactionService($projService, $costService);
    $confirmService = new DeliveryConfirmationService($txService);

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
        'subject' => 'Furniture delivery',
        'status' => SalesOrderStatus::Confirmed,
        'currency' => 'IDR',
        'total_amount' => 500000,
    ]);

    $soItem = SalesOrderItem::create([
        'sales_order_id' => $so->id,
        'sku' => 'CHAIR-001',
        'description' => 'CHAIR-001',
        'quantity' => 5.00,
        'unit' => 'pcs',
        'unit_price' => 100000.00,
        'total_price' => 500000.00,
    ]);

    // Setup inventory item to test returned stock adjustment later
    $invItem = InventoryItem::create([
        'warehouse_id' => $wh->id,
        'sku' => 'CHAIR001',
        'name' => 'Comfortable Chair',
        'quantity_current' => 10.00,
        'quantity_reserved' => 0.00,
        'avg_cost_price' => 80000.00,
    ]);

    // 1. Create DO and Approve
    $do = $doService->create([
        'sales_order_id' => $so->id,
        'warehouse_id' => $wh->id,
        'customer_id' => $so->customer_id,
        'items' => [
            [
                'sales_order_item_id' => $soItem->id,
                'quantity_requested' => 3.00,
            ],
        ],
    ]);

    $doService->approve($do);
    expect($do->refresh()->status)->toBe('approved');

    // 2. Create Shipment
    $shipData = [
        'courier_type' => 'internal',
        'estimated_cost' => 50000.00,
        'items' => [
            [
                'delivery_order_item_id' => $do->items->first()->id,
                'quantity_shipped' => 3.00,
            ],
        ],
    ];

    $shipment = $shipService->createShipment($do, $shipData);
    expect($shipment->status)->toBe('pending_dispatch')
        ->and($do->refresh()->status)->toBe('shipped');

    // 3. Dispatch Shipment
    $shipService->dispatch($shipment);
    expect($shipment->refresh()->status)->toBe('in_transit');

    // 4. Confirm Delivery
    $confirmService->confirmDelivery($shipment, [
        'receiver_name' => 'John Doe',
        'receiver_signature' => 'data:image/png;base64,stub',
        'actual_cost' => 52000.00,
    ]);

    expect($shipment->refresh()->status)->toBe('delivered')
        ->and($do->refresh()->status)->toBe('delivered')
        ->and((float) $do->items->first()->refresh()->quantity_delivered)->toBe(3.00);
});

test('it handles returned shipment cargo and synchronizes stock', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $numGen = new DeliveryNumberGenerator;
    $valService = new DeliveryValidationService;
    $doService = new DeliveryOrderService($numGen, $valService);
    $shipService = new ShipmentService($numGen);

    $projService = new InventoryProjectionService;
    $costService = new CostingService;
    $txService = new InventoryTransactionService($projService, $costService);
    $confirmService = new DeliveryConfirmationService($txService);

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
        'subject' => 'Furniture delivery',
        'status' => SalesOrderStatus::Confirmed,
        'currency' => 'IDR',
        'total_amount' => 500000,
    ]);

    $soItem = SalesOrderItem::create([
        'sales_order_id' => $so->id,
        'sku' => 'CHAIR-001',
        'description' => 'CHAIR-001',
        'quantity' => 5.00,
        'unit' => 'pcs',
        'unit_price' => 100000.00,
        'total_price' => 500000.00,
    ]);

    $invItem = InventoryItem::create([
        'warehouse_id' => $wh->id,
        'sku' => 'CHAIR001',
        'name' => 'Comfortable Chair',
        'quantity_current' => 10.00,
        'quantity_reserved' => 0.00,
        'avg_cost_price' => 80000.00,
    ]);

    $do = $doService->create([
        'sales_order_id' => $so->id,
        'warehouse_id' => $wh->id,
        'customer_id' => $so->customer_id,
        'items' => [
            [
                'sales_order_item_id' => $soItem->id,
                'quantity_requested' => 3.00,
            ],
        ],
    ]);

    $doService->approve($do);
    $shipment = $shipService->createShipment($do, [
        'courier_type' => 'internal',
        'estimated_cost' => 50000.00,
        'items' => [
            [
                'delivery_order_item_id' => $do->items->first()->id,
                'quantity_shipped' => 3.00,
            ],
        ],
    ]);

    $shipService->dispatch($shipment);

    // 5. Process Return
    $confirmService->processReturn($shipment, 'Customer address unreachable');

    expect($shipment->refresh()->status)->toBe('returned')
        ->and($do->refresh()->status)->toBe('approved')
        ->and((float) $do->items->first()->refresh()->quantity_shipped)->toBe(0.00)
        ->and((float) $invItem->refresh()->quantity_current)->toBe(13.00);
});
