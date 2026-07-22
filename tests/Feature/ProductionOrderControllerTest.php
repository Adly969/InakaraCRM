<?php

namespace Tests\Feature;

use App\Enums\ProductionOrderStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\ProductionOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * @property User $owner
 */
class ProductionOrderControllerTest extends TestCase {}

/** @var ProductionOrderControllerTest $this */
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');
    $this->owner = User::factory()->create()->assignRole(UserRole::Owner->value);
});

test('unauthorized users cannot view production orders index', function () {
    $guest = User::factory()->create();

    $this->actingAs($guest)
        ->get(route('production-orders.index'))
        ->assertStatus(403);
});

test('authorized users can view production orders index', function () {
    $po = ProductionOrder::factory()->create();

    $this->actingAs($this->owner)
        ->get(route('production-orders.index'))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('production-orders/index')
            ->has('productionOrders.data')
        );
});

test('it renders create form', function () {
    $this->actingAs($this->owner)
        ->get(route('production-orders.create'))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('production-orders/create')
            ->has('customers')
            ->has('users')
        );
});

test('it stores a new production order', function () {
    $customer = Customer::factory()->create();
    $data = [
        'customer_id' => $customer->id,
        'subject' => 'Oak Cabinet Manufacture',
        'priority' => 'normal',
        'currency' => 'IDR',
        'tax_rate' => 11,
        'items' => [
            [
                'description' => 'Cabinet doors',
                'quantity' => 4,
                'unit' => 'pcs',
                'unit_price' => 250000,
            ],
        ],
    ];

    $this->actingAs($this->owner)
        ->post(route('production-orders.store'), $data)
        ->assertRedirect();

    $this->assertDatabaseHas('production_orders', [
        'subject' => 'Oak Cabinet Manufacture',
    ]);
});

test('it transitions status via update controller action', function () {
    $po = ProductionOrder::factory()->create([
        'status' => ProductionOrderStatus::Draft,
        'target_completion_date' => now()->addDays(5)->format('Y-m-d'),
    ]);

    $data = [
        'customer_id' => $po->customer_id,
        'subject' => $po->subject,
        'priority' => $po->priority->value,
        'currency' => $po->currency,
        'tax_rate' => (float) $po->tax_rate,
        'status' => 'scheduled',
        'target_completion_date' => now()->addDays(5)->format('Y-m-d'),
        '_updated_at' => $po->updated_at->toIso8601String(),
        'items' => [
            [
                'description' => 'Cabinet doors',
                'quantity' => 4,
                'unit' => 'pcs',
                'unit_price' => 250000,
            ],
        ],
    ];

    $this->actingAs($this->owner)
        ->put(route('production-orders.update', $po->id), $data)
        ->assertRedirect();

    expect($po->fresh()->status)->toBe(ProductionOrderStatus::Scheduled);
});
