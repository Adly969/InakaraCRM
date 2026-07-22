<?php

use App\Enums\CustomerStatus;
use App\Models\Customer;
use App\Models\User;
use App\Services\CustomerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it creates a customer and generates a reference number', function () {
    $creator = User::factory()->create();
    $service = new CustomerService;

    $data = [
        'name' => 'Jane Smith',
        'company_name' => 'Acme Inc',
        'email' => 'jane@acme.com',
        'phone' => '0987654321',
        'type' => 'organization',
        'status' => CustomerStatus::Active,
        'notes' => 'Some test notes',
    ];

    $customer = $service->create($data, $creator);

    expect($customer)->toBeInstanceOf(Customer::class)
        ->and($customer->name)->toBe('Jane Smith')
        ->and($customer->company_name)->toBe('Acme Inc')
        ->and($customer->email)->toBe('jane@acme.com')
        ->and($customer->phone)->toBe('0987654321')
        ->and($customer->type)->toBe('organization')
        ->and($customer->status)->toBe(CustomerStatus::Active)
        ->and($customer->notes)->toBe('Some test notes')
        ->and($customer->created_by)->toBe($creator->id)
        ->and($customer->reference_no)->toBe('CS-000001');

    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'reference_no' => 'CS-000001',
        'created_by' => $creator->id,
    ]);
});

test('it updates a customer and sets updated_by field', function () {
    $customer = Customer::factory()->create();
    $updater = User::factory()->create();
    $service = new CustomerService;

    $data = [
        'name' => 'Jane Updated',
        'email' => 'jane.updated@acme.com',
    ];

    $updatedCustomer = $service->update($customer, $data, $updater);

    expect($updatedCustomer->name)->toBe('Jane Updated')
        ->and($updatedCustomer->email)->toBe('jane.updated@acme.com')
        ->and($updatedCustomer->updated_by)->toBe($updater->id);

    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'name' => 'Jane Updated',
        'updated_by' => $updater->id,
    ]);
});
