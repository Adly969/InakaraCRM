<?php

use App\Enums\SalesOrderStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');
});

test('owners and managers can view and modify any sales order', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $manager = User::factory()->create()->assignRole(UserRole::Manager->value);

    $customer = Customer::factory()->create();
    $salesOrder = SalesOrder::create([
        'customer_id' => $customer->id,
        'subject' => 'Policy Check',
        'currency' => 'IDR',
        'status' => SalesOrderStatus::Draft,
    ]);

    expect(Gate::forUser($owner)->allows('view', $salesOrder))->toBeTrue()
        ->and(Gate::forUser($owner)->allows('update', $salesOrder))->toBeTrue()
        ->and(Gate::forUser($manager)->allows('view', $salesOrder))->toBeTrue()
        ->and(Gate::forUser($manager)->allows('update', $salesOrder))->toBeTrue();
});

test('sales reps can only view and update their own sales orders', function () {
    $sales1 = User::factory()->create()->assignRole(UserRole::Sales->value);
    $sales2 = User::factory()->create()->assignRole(UserRole::Sales->value);

    $customer = Customer::factory()->create();
    $salesOrder = SalesOrder::create([
        'customer_id' => $customer->id,
        'subject' => 'Owned By Sales 1',
        'currency' => 'IDR',
        'status' => SalesOrderStatus::Draft,
        'assigned_to' => $sales1->id,
    ]);

    expect(Gate::forUser($sales1)->allows('view', $salesOrder))->toBeTrue()
        ->and(Gate::forUser($sales1)->allows('update', $salesOrder))->toBeTrue()
        ->and(Gate::forUser($sales2)->allows('view', $salesOrder))->toBeFalse()
        ->and(Gate::forUser($sales2)->allows('update', $salesOrder))->toBeFalse();
});

test('customer service can view all but cannot modify', function () {
    $cs = User::factory()->create()->assignRole(UserRole::CustomerService->value);

    $customer = Customer::factory()->create();
    $salesOrder = SalesOrder::create([
        'customer_id' => $customer->id,
        'subject' => 'CS Read Only',
        'currency' => 'IDR',
        'status' => SalesOrderStatus::Draft,
    ]);

    expect(Gate::forUser($cs)->allows('view', $salesOrder))->toBeTrue()
        ->and(Gate::forUser($cs)->allows('update', $salesOrder))->toBeFalse();
});
