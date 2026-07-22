<?php

use App\Enums\SalesOrderStatus;
use App\Enums\WarehouseStatus;
use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\DeliveryNumberGenerator;
use App\Services\DeliveryOrderService;
use App\Services\DeliveryValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('it creates a delivery order as draft and snapshots addresses', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $numGen = new DeliveryNumberGenerator;
    $valService = new DeliveryValidationService;
    $service = new DeliveryOrderService($numGen, $valService);

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
        'description' => 'Comfortable Chair',
        'quantity' => 5.00,
        'unit' => 'pcs',
        'unit_price' => 100000.00,
        'total_price' => 500000.00,
    ]);

    $data = [
        'sales_order_id' => $so->id,
        'warehouse_id' => $wh->id,
        'customer_id' => $so->customer_id,
        'shipping_address' => '123 Shipping Lane',
        'billing_address' => '456 Billing Ave',
        'items' => [
            [
                'sales_order_item_id' => $soItem->id,
                'quantity_requested' => 3.00,
            ],
        ],
    ];

    $do = $service->create($data);

    expect($do->reference_no)->toContain('DO-')
        ->and($do->status)->toBe('draft')
        ->and($do->shipping_address_snapshot['address'])->toBe('123 Shipping Lane')
        ->and($do->billing_address_snapshot['address'])->toBe('456 Billing Ave')
        ->and($do->items)->toHaveCount(1)
        ->and((float) $do->items->first()->quantity_requested)->toBe(3.00);
});

test('it prevents creation when quantity requested exceeds outstanding quantity', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $numGen = new DeliveryNumberGenerator;
    $valService = new DeliveryValidationService;
    $service = new DeliveryOrderService($numGen, $valService);

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
        'description' => 'Comfortable Chair',
        'quantity' => 2.00,
        'unit' => 'pcs',
        'unit_price' => 100000.00,
        'total_price' => 200000.00,
    ]);

    $data = [
        'sales_order_id' => $so->id,
        'warehouse_id' => $wh->id,
        'customer_id' => $so->customer_id,
        'items' => [
            [
                'sales_order_item_id' => $soItem->id,
                'quantity_requested' => 3.00,
            ],
        ],
    ];

    expect(fn () => $service->create($data))->toThrow(ValidationException::class);
});
