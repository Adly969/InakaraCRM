<?php

use App\Enums\CustomerStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');
});

test('unauthenticated users are redirected to login', function () {
    $this->get(route('customers.index'))->assertRedirect(route('login'));
});

test('index page lists all customers for owners, but only assigned customers for sales', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $sales = User::factory()->create()->assignRole(UserRole::Sales->value);

    $customer1 = Customer::factory()->create(['assigned_to' => $sales->id]);
    $customer2 = Customer::factory()->create(); // unassigned

    // Owner view
    $response = $this->actingAs($owner)->get(route('customers.index'));
    $response->assertStatus(200);
    $page = $response->original->getData()['page'];
    expect($page['component'])->toBe('customers/index')
        ->and($page['props']['customers']['data'])->toHaveCount(2);

    // Sales view
    $response = $this->actingAs($sales)->get(route('customers.index'));
    $response->assertStatus(200);
    $page = $response->original->getData()['page'];
    expect($page['component'])->toBe('customers/index')
        ->and($page['props']['customers']['data'])->toHaveCount(1);
});

test('create page renders with users list', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    User::factory()->count(2)->create();

    $response = $this->actingAs($owner)->get(route('customers.create'));
    $response->assertStatus(200);
    $page = $response->original->getData()['page'];
    expect($page['component'])->toBe('customers/create')
        ->and($page['props']['users'])->not->toBeNull();
});

test('store saves a new customer and generates reference number', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);

    $data = [
        'name' => 'Acme Customer',
        'company_name' => 'Acme Corp',
        'email' => 'acme@customer.com',
        'phone' => '12345678',
        'type' => 'organization',
        'status' => CustomerStatus::Active->value,
    ];

    $this->actingAs($owner)
        ->post(route('customers.store'), $data)
        ->assertRedirect(route('customers.index'));

    $this->assertDatabaseHas('customers', [
        'name' => 'Acme Customer',
        'reference_no' => 'CS-000001',
        'created_by' => $owner->id,
    ]);
});

test('show renders customer details page', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $customer = Customer::factory()->create();

    $response = $this->actingAs($owner)->get(route('customers.show', $customer));
    $response->assertStatus(200);
    $page = $response->original->getData()['page'];
    expect($page['component'])->toBe('customers/show')
        ->and($page['props']['customer']['id'])->toBe($customer->id);
});

test('edit page renders with user list and customer data', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $customer = Customer::factory()->create();

    $response = $this->actingAs($owner)->get(route('customers.edit', $customer));
    $response->assertStatus(200);
    $page = $response->original->getData()['page'];
    expect($page['component'])->toBe('customers/edit')
        ->and($page['props']['customer']['id'])->toBe($customer->id)
        ->and($page['props']['users'])->not->toBeNull();
});

test('update modifies the customer attributes', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $customer = Customer::factory()->create();

    $data = [
        'name' => 'Acme Updated',
        'company_name' => 'Acme Corp',
        'email' => 'acme@customer.com',
        'phone' => '12345678',
        'type' => 'organization',
        'status' => CustomerStatus::Inactive->value,
    ];

    $this->actingAs($owner)
        ->put(route('customers.update', $customer), $data)
        ->assertRedirect(route('customers.index'));

    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'name' => 'Acme Updated',
        'status' => CustomerStatus::Inactive->value,
        'updated_by' => $owner->id,
    ]);
});

test('sales cannot delete customers', function () {
    $sales = User::factory()->create()->assignRole(UserRole::Sales->value);
    $customer = Customer::factory()->create();

    $this->actingAs($sales)
        ->delete(route('customers.destroy', $customer))
        ->assertStatus(403);
});

test('owner can delete customers', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $customer = Customer::factory()->create();

    $this->actingAs($owner)
        ->delete(route('customers.destroy', $customer))
        ->assertRedirect(route('customers.index'));

    $this->assertSoftDeleted('customers', ['id' => $customer->id]);
});
