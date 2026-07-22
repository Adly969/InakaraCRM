<?php

use App\Enums\SalesOrderStatus;
use App\Enums\WarehouseStatus;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Warehouse;
use App\Services\InventoryProjectionService;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('it throws validation exception when physical stock is insufficient for reservation', function () {
    $wh = Warehouse::create([
        'code' => 'WH-TEST',
        'name' => 'Test Warehouse',
        'type' => 'central',
        'status' => WarehouseStatus::Active,
        'is_default' => true,
    ]);

    $customer = Customer::factory()->create();
    $so = SalesOrder::create([
        'reference_no' => 'SO-TEST-RESERVE',
        'customer_id' => $customer->id,
        'subject' => 'Reservation Test',
        'status' => SalesOrderStatus::Draft,
        'currency' => 'IDR',
        'total_amount' => 100000,
    ]);

    $soItem = SalesOrderItem::create([
        'sales_order_id' => $so->id,
        'sku' => 'LAPTOP-01',
        'description' => 'Laptop Gamer Edition',
        'quantity' => 1.00,
        'unit' => 'pcs',
        'unit_price' => 100000.00,
        'total_price' => 100000.00,
    ]);

    // Setup item with 0 stock
    $invItem = InventoryItem::create([
        'warehouse_id' => $wh->id,
        'sku' => 'LAPTOP-GAMER-EDITION',
        'name' => 'Laptop Gamer Edition',
        'quantity_current' => 0.00,
        'quantity_reserved' => 0.00,
        'avg_cost_price' => 80000.00,
    ]);

    $projService = new InventoryProjectionService;
    $resService = new ReservationService($projService);

    // Expect validation exception for insufficient stock
    expect(fn () => $resService->reserveStock($so))
        ->toThrow(ValidationException::class);
});

test('it successfully reserves stock when physical stock is sufficient', function () {
    $wh = Warehouse::create([
        'code' => 'WH-TEST',
        'name' => 'Test Warehouse',
        'type' => 'central',
        'status' => WarehouseStatus::Active,
        'is_default' => true,
    ]);

    $customer = Customer::factory()->create();
    $so = SalesOrder::create([
        'reference_no' => 'SO-TEST-RESERVE-OK',
        'customer_id' => $customer->id,
        'subject' => 'Reservation Test',
        'status' => SalesOrderStatus::Draft,
        'currency' => 'IDR',
        'total_amount' => 100000,
    ]);

    $soItem = SalesOrderItem::create([
        'sales_order_id' => $so->id,
        'sku' => 'LAPTOP-01',
        'description' => 'Laptop Gamer Edition',
        'quantity' => 1.00,
        'unit' => 'pcs',
        'unit_price' => 100000.00,
        'total_price' => 100000.00,
    ]);

    // Setup item with 10 stock
    $invItem = InventoryItem::create([
        'warehouse_id' => $wh->id,
        'sku' => 'LAPTOP-GAMER-EDITION',
        'name' => 'Laptop Gamer Edition',
        'quantity_current' => 10.00,
        'quantity_reserved' => 0.00,
        'avg_cost_price' => 80000.00,
    ]);

    $projService = new InventoryProjectionService;
    $resService = new ReservationService($projService);

    $resService->reserveStock($so);

    expect((float) $invItem->refresh()->quantity_reserved)->toBe(1.00);
});
