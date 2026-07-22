<?php

use App\Enums\ProductionOrderStatus;
use App\Enums\ProductionPriority;
use App\Enums\SalesOrderStatus;
use App\Events\ProductionOrderCreated;
use App\Models\Customer;
use App\Models\ProductionOrder;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\User;
use App\Services\ProductionOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('it creates a production order and generates reference', function () {
    Event::fake();

    $creator = User::factory()->create();
    $customer = Customer::factory()->create();
    $service = new ProductionOrderService;

    $data = [
        'customer_id' => $customer->id,
        'subject' => 'Oak Chair Production',
        'priority' => ProductionPriority::Normal,
        'currency' => 'IDR',
        'tax_rate' => 11,
        'items' => [
            [
                'description' => 'Solid Oak Backrest',
                'quantity' => 10,
                'unit' => 'pcs',
                'unit_price' => 100000,
            ],
        ],
    ];

    $po = $service->create($data, $creator);

    expect($po)->toBeInstanceOf(ProductionOrder::class)
        ->and($po->reference_no)->toBe('PO-000001')
        ->and($po->status)->toBe(ProductionOrderStatus::Draft)
        ->and($po->subtotal)->toEqual(1000000.00)
        ->and($po->tax_amount)->toEqual(110000.00)
        ->and($po->total_amount)->toEqual(1110000.00);

    $this->assertDatabaseHas('production_orders', [
        'id' => $po->id,
        'reference_no' => 'PO-000001',
    ]);

    $this->assertDatabaseHas('production_order_logs', [
        'production_order_id' => $po->id,
        'status_to' => 'draft',
    ]);

    Event::assertDispatched(ProductionOrderCreated::class);
});

test('it enforces transition status matrix constraints', function () {
    $creator = User::factory()->create();
    $po = ProductionOrder::factory()->create([
        'status' => ProductionOrderStatus::Draft,
        'target_completion_date' => null,
    ]);
    $service = new ProductionOrderService;

    // Invalid transition: Draft to InProduction directly
    expect(fn () => $service->transitionStatus($po, ProductionOrderStatus::InProduction, $creator))
        ->toThrow(DomainException::class, 'Invalid status transition from Draft to In Production.');

    // Valid transition: Draft to Scheduled (requires target_completion_date)
    expect(fn () => $service->transitionStatus($po, ProductionOrderStatus::Scheduled, $creator))
        ->toThrow(InvalidArgumentException::class, 'A target completion date is required when scheduling production.');

    $po->target_completion_date = now()->addDays(7);
    $po->save();

    $service->transitionStatus($po, ProductionOrderStatus::Scheduled, $creator);
    expect($po->status)->toBe(ProductionOrderStatus::Scheduled);

    $this->assertDatabaseHas('production_order_logs', [
        'production_order_id' => $po->id,
        'status_from' => 'draft',
        'status_to' => 'scheduled',
    ]);
});

test('it enforces optimistic locking during update', function () {
    $creator = User::factory()->create();
    $po = ProductionOrder::factory()->create(['status' => ProductionOrderStatus::Draft]);
    $service = new ProductionOrderService;

    // Simulate stale updated_at value
    $staleData = [
        'customer_id' => $po->customer_id,
        'subject' => 'Stale Subject',
        'currency' => 'IDR',
        '_updated_at' => $po->updated_at->subMinutes(10)->toIso8601String(),
        'items' => [],
    ];

    expect(fn () => $service->update($po, $staleData, $creator))
        ->toThrow(ValidationException::class);
});

test('it converts confirmed sales order to production order with item mapping', function () {
    Event::fake();

    $creator = User::factory()->create();
    $customer = Customer::factory()->create();

    // Create sales order and item manually
    $so = SalesOrder::create([
        'customer_id' => $customer->id,
        'subject' => 'Commercial Fitting Deal',
        'status' => SalesOrderStatus::Confirmed->value,
        'currency' => 'IDR',
        'tax_rate' => 11.00,
        'subtotal' => 2000000,
        'tax_amount' => 220000,
        'total_amount' => 2220000,
        'reference_no' => 'SO-000001',
    ]);

    $soItem = SalesOrderItem::create([
        'sales_order_id' => $so->id,
        'description' => 'Fitted Wardrobe',
        'quantity' => 1,
        'unit' => 'unit',
        'unit_price' => 2000000,
        'total_price' => 2000000,
    ]);

    $service = new ProductionOrderService;
    $po = $service->createFromSalesOrder($so, $creator);

    expect($po)->toBeInstanceOf(ProductionOrder::class)
        ->and($po->sales_order_id)->toBe($so->id)
        ->and($po->total_amount)->toEqual(2220000.00)
        ->and($po->items->first()->sales_order_item_id)->toBe($soItem->id);

    // Assert double conversion is blocked
    expect(fn () => $service->createFromSalesOrder($so, $creator))
        ->toThrow(DomainException::class, 'This Sales Order has already been converted to a Production Order.');
});
