<?php

use App\Enums\QuotationStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');
});

test('unauthenticated users are redirected to login', function () {
    $this->get(route('quotations.index'))->assertRedirect(route('login'));
});

test('index page lists all quotations for owners, but only assigned quotations for sales', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $sales = User::factory()->create()->assignRole(UserRole::Sales->value);

    $customer = Customer::factory()->create();
    $quotation1 = Quotation::factory()->create([
        'customer_id' => $customer->id,
        'assigned_to' => $sales->id,
    ]);
    $quotation2 = Quotation::factory()->create([
        'customer_id' => $customer->id,
    ]); // unassigned

    // Owner view
    $response = $this->actingAs($owner)->get(route('quotations.index'));
    $response->assertStatus(200);
    $page = $response->original->getData()['page'];
    expect($page['component'])->toBe('quotations/index')
        ->and($page['props']['quotations']['data'])->toHaveCount(2);

    // Sales view
    $response = $this->actingAs($sales)->get(route('quotations.index'));
    $response->assertStatus(200);
    $page = $response->original->getData()['page'];
    expect($page['component'])->toBe('quotations/index')
        ->and($page['props']['quotations']['data'])->toHaveCount(1);
});

test('create page renders with select lists', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);

    $response = $this->actingAs($owner)->get(route('quotations.create'));
    $response->assertStatus(200);
    $page = $response->original->getData()['page'];
    expect($page['component'])->toBe('quotations/create')
        ->and($page['props']['customers'])->not->toBeNull()
        ->and($page['props']['leads'])->not->toBeNull()
        ->and($page['props']['users'])->not->toBeNull();
});

test('store saves a new quotation and line items within a transaction', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $customer = Customer::factory()->create();

    $data = [
        'customer_id' => $customer->id,
        'subject' => 'Store Test Proposal',
        'valid_until' => '2026-12-31',
        'currency' => 'IDR',
        'tax_rate' => 11,
        'status' => QuotationStatus::Draft->value,
        'items' => [
            [
                'description' => 'Cabinet Sideboard Table',
                'quantity' => 2,
                'unit' => 'pcs',
                'unit_price' => 50000.00,
            ],
            [
                'description' => 'Assembly and Installation Fees',
                'quantity' => 1,
                'unit' => 'lot',
                'unit_price' => 15000.00,
            ],
        ],
    ];

    $this->actingAs($owner)
        ->post(route('quotations.store'), $data)
        ->assertRedirect(route('quotations.index'));

    $this->assertDatabaseHas('quotations', [
        'subject' => 'Store Test Proposal',
        'reference_no' => 'QT-000001',
        'tax_rate' => 11.00,
        'subtotal' => 115000.00,
        'tax_amount' => 12650.00,
        'total_amount' => 127650.00,
        'created_by' => $owner->id,
    ]);

    $this->assertDatabaseHas('quotation_items', [
        'description' => 'Cabinet Sideboard Table',
        'quantity' => 2.00,
        'total_price' => 100000.00,
    ]);
});

test('show page renders quotation and items details', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $customer = Customer::factory()->create();
    $quotation = Quotation::factory()->create(['customer_id' => $customer->id]);
    $item = QuotationItem::factory()->create([
        'quotation_id' => $quotation->id,
        'unit_price' => 10000,
        'quantity' => 1,
        'total_price' => 10000,
    ]);

    $response = $this->actingAs($owner)->get(route('quotations.show', $quotation));
    $response->assertStatus(200);
    $page = $response->original->getData()['page'];
    expect($page['component'])->toBe('quotations/show')
        ->and($page['props']['quotation']['id'])->toBe($quotation->id);
});

test('edit page redirects to show if quotation is locked', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $customer = Customer::factory()->create();

    // Test Sent (locked)
    $quotation = Quotation::factory()->create([
        'customer_id' => $customer->id,
        'status' => QuotationStatus::Sent,
    ]);

    $this->actingAs($owner)
        ->get(route('quotations.edit', $quotation))
        ->assertRedirect(route('quotations.show', $quotation));
});

test('update modifies draft quotation details and replaces items list', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $customer = Customer::factory()->create();
    $quotation = Quotation::factory()->create([
        'customer_id' => $customer->id,
        'status' => QuotationStatus::Draft,
    ]);
    $oldItem = QuotationItem::factory()->create([
        'quotation_id' => $quotation->id,
        'description' => 'Old Item',
    ]);

    $data = [
        'customer_id' => $customer->id,
        'subject' => 'Updated Subject',
        'valid_until' => '2026-12-31',
        'currency' => 'IDR',
        'tax_rate' => 11,
        'status' => QuotationStatus::Draft->value,
        'items' => [
            [
                'description' => 'Brand New Item',
                'quantity' => 1,
                'unit' => 'pcs',
                'unit_price' => 12000.00,
            ],
        ],
    ];

    $this->actingAs($owner)
        ->put(route('quotations.update', $quotation), $data)
        ->assertRedirect(route('quotations.index'));

    $this->assertDatabaseHas('quotations', [
        'id' => $quotation->id,
        'subject' => 'Updated Subject',
        'subtotal' => 12000.00,
        'total_amount' => 13320.00,
        'updated_by' => $owner->id,
    ]);

    // Check old item was deleted and replaced
    $this->assertDatabaseMissing('quotation_items', ['id' => $oldItem->id]);
    $this->assertDatabaseHas('quotation_items', [
        'quotation_id' => $quotation->id,
        'description' => 'Brand New Item',
    ]);
});

test('owner can delete quotation', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $customer = Customer::factory()->create();
    $quotation = Quotation::factory()->create(['customer_id' => $customer->id]);

    $this->actingAs($owner)
        ->delete(route('quotations.destroy', $quotation))
        ->assertRedirect(route('quotations.index'));

    $this->assertSoftDeleted('quotations', ['id' => $quotation->id]);
});
