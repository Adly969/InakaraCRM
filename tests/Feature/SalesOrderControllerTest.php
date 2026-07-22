<?php

use App\Enums\SalesOrderStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');
});

test('unauthenticated users are redirected to login', function () {
    $this->get(route('sales-orders.index'))->assertRedirect(route('login'));
});

test('index page lists all sales orders for owners, but only assigned for sales reps', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $sales = User::factory()->create()->assignRole(UserRole::Sales->value);

    $customer = Customer::factory()->create();

    $so1 = SalesOrder::create([
        'customer_id' => $customer->id,
        'subject' => 'Assigned SO',
        'currency' => 'IDR',
        'status' => SalesOrderStatus::Draft,
        'assigned_to' => $sales->id,
    ]);

    $so2 = SalesOrder::create([
        'customer_id' => $customer->id,
        'subject' => 'Unassigned SO',
        'currency' => 'IDR',
        'status' => SalesOrderStatus::Draft,
    ]);

    // Owner view
    $response = $this->actingAs($owner)->get(route('sales-orders.index'));
    $response->assertStatus(200);
    $page = $response->original->getData()['page'];
    expect($page['component'])->toBe('sales-orders/index')
        ->and($page['props']['salesOrders']['data'])->toHaveCount(2);

    // Sales view
    $response = $this->actingAs($sales)->get(route('sales-orders.index'));
    $response->assertStatus(200);
    $page = $response->original->getData()['page'];
    expect($page['component'])->toBe('sales-orders/index')
        ->and($page['props']['salesOrders']['data'])->toHaveCount(1);
});

test('create page renders with select lists', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);

    $response = $this->actingAs($owner)->get(route('sales-orders.create'));
    $response->assertStatus(200);
    $page = $response->original->getData()['page'];
    expect($page['component'])->toBe('sales-orders/create')
        ->and($page['props']['customers'])->not->toBeNull()
        ->and($page['props']['users'])->not->toBeNull();
});

test('store saves a new sales order and items within a transaction', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $customer = Customer::factory()->create();

    $data = [
        'customer_id' => $customer->id,
        'subject' => 'Store SO Proposal',
        'currency' => 'IDR',
        'tax_rate' => 11,
        'status' => SalesOrderStatus::Draft->value,
        'items' => [
            [
                'description' => 'Product X',
                'quantity' => 5,
                'unit' => 'pcs',
                'unit_price' => 10000.00,
            ],
        ],
    ];

    $this->actingAs($owner)
        ->post(route('sales-orders.store'), $data)
        ->assertRedirect(route('sales-orders.index'));

    $this->assertDatabaseHas('sales_orders', [
        'subject' => 'Store SO Proposal',
        'reference_no' => 'SO-000001',
        'tax_rate' => 11.00,
        'subtotal' => 50000.00,
        'tax_amount' => 5500.00,
        'total_amount' => 55500.00,
        'created_by' => $owner->id,
    ]);

    $this->assertDatabaseHas('sales_order_items', [
        'description' => 'Product X',
        'quantity' => 5.00,
        'total_price' => 50000.00,
    ]);
});

test('show page renders sales order details', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $customer = Customer::factory()->create();

    $salesOrder = SalesOrder::create([
        'customer_id' => $customer->id,
        'subject' => 'Show Test Order',
        'currency' => 'IDR',
        'status' => SalesOrderStatus::Draft,
    ]);

    $response = $this->actingAs($owner)->get(route('sales-orders.show', $salesOrder));
    $response->assertStatus(200);
    $page = $response->original->getData()['page'];
    expect($page['component'])->toBe('sales-orders/show')
        ->and($page['props']['salesOrder']['id'])->toBe($salesOrder->id);
});

test('edit page redirects to show if sales order is locked', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $customer = Customer::factory()->create();

    $salesOrder = SalesOrder::create([
        'customer_id' => $customer->id,
        'subject' => 'Locked Confirmed SO',
        'currency' => 'IDR',
        'status' => SalesOrderStatus::Confirmed,
    ]);

    $this->actingAs($owner)
        ->get(route('sales-orders.edit', $salesOrder))
        ->assertRedirect(route('sales-orders.show', $salesOrder));
});

test('update modifies draft details and replaces items', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $customer = Customer::factory()->create();

    $salesOrder = SalesOrder::create([
        'customer_id' => $customer->id,
        'subject' => 'Draft SO',
        'currency' => 'IDR',
        'status' => SalesOrderStatus::Draft,
    ]);

    $oldItem = SalesOrderItem::create([
        'sales_order_id' => $salesOrder->id,
        'description' => 'Old SO Item',
        'quantity' => 1,
        'unit' => 'pcs',
        'unit_price' => 1000.00,
        'total_price' => 1000.00,
    ]);

    $data = [
        'customer_id' => $customer->id,
        'subject' => 'Updated SO Subject',
        'currency' => 'IDR',
        'tax_rate' => 10,
        'status' => SalesOrderStatus::Draft->value,
        'items' => [
            [
                'description' => 'New Replacement Item',
                'quantity' => 2,
                'unit' => 'pcs',
                'unit_price' => 3000.00,
            ],
        ],
    ];

    $this->actingAs($owner)
        ->put(route('sales-orders.update', $salesOrder), $data)
        ->assertRedirect(route('sales-orders.show', $salesOrder));

    $this->assertDatabaseHas('sales_orders', [
        'id' => $salesOrder->id,
        'subject' => 'Updated SO Subject',
        'subtotal' => 6000.00,
        'total_amount' => 6600.00,
    ]);

    $this->assertDatabaseMissing('sales_order_items', ['id' => $oldItem->id]);
    $this->assertDatabaseHas('sales_order_items', [
        'sales_order_id' => $salesOrder->id,
        'description' => 'New Replacement Item',
        'total_price' => 6000.00,
    ]);
});

test('owner can delete sales order', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $customer = Customer::factory()->create();

    $salesOrder = SalesOrder::create([
        'customer_id' => $customer->id,
        'subject' => 'Delete Test Order',
        'currency' => 'IDR',
        'status' => SalesOrderStatus::Draft,
    ]);

    $this->actingAs($owner)
        ->delete(route('sales-orders.destroy', $salesOrder))
        ->assertRedirect(route('sales-orders.index'));

    $this->assertSoftDeleted('sales_orders', ['id' => $salesOrder->id]);
});

test('owner can manually release credit hold on sales order', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $customer = Customer::factory()->create();

    $salesOrder = SalesOrder::create([
        'company_id' => 1,
        'branch_id' => 1,
        'customer_id' => $customer->id,
        'subject' => 'Locked Credit Hold Order',
        'currency' => 'IDR',
        'status' => SalesOrderStatus::Draft,
        'credit_hold_status' => 'hold',
    ]);

    $this->actingAs($owner)
        ->post(route('sales-orders.release-credit', $salesOrder), [
            'reason' => 'Override for VIP client request',
        ])
        ->assertRedirect(route('sales-orders.show', $salesOrder));

    expect($salesOrder->fresh()->credit_hold_status)->toBe('released')
        ->and($salesOrder->fresh()->credit_hold_override_reason)->toBe('Override for VIP client request')
        ->and($salesOrder->fresh()->credit_hold_released_by)->toBe($owner->id);
});
