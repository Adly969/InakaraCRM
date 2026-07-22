<?php

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');
});

test('viewAny allows users with view-customers permission', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $sales = User::factory()->create()->assignRole(UserRole::Sales->value);
    $finance = User::factory()->create()->assignRole(UserRole::Finance->value);

    expect(Gate::forUser($owner)->allows('viewAny', Customer::class))->toBeTrue()
        ->and(Gate::forUser($sales)->allows('viewAny', Customer::class))->toBeTrue()
        ->and(Gate::forUser($finance)->allows('viewAny', Customer::class))->toBeFalse();
});

test('view allows owner, admin, manager, customer-service to view all, sales to view only assigned', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $manager = User::factory()->create()->assignRole(UserRole::Manager->value);
    $cs = User::factory()->create()->assignRole(UserRole::CustomerService->value);
    $sales = User::factory()->create()->assignRole(UserRole::Sales->value);
    $salesOther = User::factory()->create()->assignRole(UserRole::Sales->value);

    $customerAssignedToSales = Customer::factory()->create(['assigned_to' => $sales->id]);
    $customerUnassigned = Customer::factory()->create();

    // Owner can view all
    expect(Gate::forUser($owner)->allows('view', $customerAssignedToSales))->toBeTrue()
        ->and(Gate::forUser($owner)->allows('view', $customerUnassigned))->toBeTrue();

    // Manager can view all
    expect(Gate::forUser($manager)->allows('view', $customerAssignedToSales))->toBeTrue()
        ->and(Gate::forUser($manager)->allows('view', $customerUnassigned))->toBeTrue();

    // Customer Service can view all
    expect(Gate::forUser($cs)->allows('view', $customerAssignedToSales))->toBeTrue()
        ->and(Gate::forUser($cs)->allows('view', $customerUnassigned))->toBeTrue();

    // Sales can only view assigned to them
    expect(Gate::forUser($sales)->allows('view', $customerAssignedToSales))->toBeTrue()
        ->and(Gate::forUser($sales)->allows('view', $customerUnassigned))->toBeFalse();

    // Other sales cannot view assigned to sales
    expect(Gate::forUser($salesOther)->allows('view', $customerAssignedToSales))->toBeFalse();
});

test('create allows user with create-customers permission', function () {
    $sales = User::factory()->create()->assignRole(UserRole::Sales->value);
    $cs = User::factory()->create()->assignRole(UserRole::CustomerService->value);

    expect(Gate::forUser($sales)->allows('create', Customer::class))->toBeTrue()
        ->and(Gate::forUser($cs)->allows('create', Customer::class))->toBeFalse();
});

test('update allows owner, admin, manager to edit all, sales to edit only assigned', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $manager = User::factory()->create()->assignRole(UserRole::Manager->value);
    $sales = User::factory()->create()->assignRole(UserRole::Sales->value);
    $salesOther = User::factory()->create()->assignRole(UserRole::Sales->value);

    $customerAssignedToSales = Customer::factory()->create(['assigned_to' => $sales->id]);
    $customerUnassigned = Customer::factory()->create();

    // Owner can edit all
    expect(Gate::forUser($owner)->allows('update', $customerAssignedToSales))->toBeTrue()
        ->and(Gate::forUser($owner)->allows('update', $customerUnassigned))->toBeTrue();

    // Manager can edit all
    expect(Gate::forUser($manager)->allows('update', $customerAssignedToSales))->toBeTrue()
        ->and(Gate::forUser($manager)->allows('update', $customerUnassigned))->toBeTrue();

    // Sales can only edit assigned to them
    expect(Gate::forUser($sales)->allows('update', $customerAssignedToSales))->toBeTrue()
        ->and(Gate::forUser($sales)->allows('update', $customerUnassigned))->toBeFalse();

    // Other sales cannot edit assigned to sales
    expect(Gate::forUser($salesOther)->allows('update', $customerAssignedToSales))->toBeFalse();
});

test('delete is restricted to owner and admin roles via permission', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $admin = User::factory()->create()->assignRole(UserRole::Admin->value);
    $manager = User::factory()->create()->assignRole(UserRole::Manager->value);
    $sales = User::factory()->create()->assignRole(UserRole::Sales->value);

    $customer = Customer::factory()->create();

    expect(Gate::forUser($owner)->allows('delete', $customer))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('delete', $customer))->toBeTrue()
        ->and(Gate::forUser($manager)->allows('delete', $customer))->toBeFalse()
        ->and(Gate::forUser($sales)->allows('delete', $customer))->toBeFalse();
});
