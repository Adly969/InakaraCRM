<?php

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');
});

test('owners, admins, managers, and customer service can view any quotation', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $admin = User::factory()->create()->assignRole(UserRole::Admin->value);
    $manager = User::factory()->create()->assignRole(UserRole::Manager->value);
    $cs = User::factory()->create()->assignRole(UserRole::CustomerService->value);
    $sales = User::factory()->create()->assignRole(UserRole::Sales->value);

    expect(Gate::forUser($owner)->allows('viewAny', Quotation::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('viewAny', Quotation::class))->toBeTrue()
        ->and(Gate::forUser($manager)->allows('viewAny', Quotation::class))->toBeTrue()
        ->and(Gate::forUser($cs)->allows('viewAny', Quotation::class))->toBeTrue()
        ->and(Gate::forUser($sales)->allows('viewAny', Quotation::class))->toBeTrue();
});

test('sales reps can only view their own assigned quotations', function () {
    $sales1 = User::factory()->create()->assignRole(UserRole::Sales->value);
    $sales2 = User::factory()->create()->assignRole(UserRole::Sales->value);
    $customer = Customer::factory()->create();

    $quotation = Quotation::factory()->create([
        'customer_id' => $customer->id,
        'assigned_to' => $sales1->id,
    ]);

    expect(Gate::forUser($sales1)->allows('view', $quotation))->toBeTrue()
        ->and(Gate::forUser($sales2)->allows('view', $quotation))->toBeFalse();
});

test('sales reps can only update their own assigned quotations', function () {
    $sales1 = User::factory()->create()->assignRole(UserRole::Sales->value);
    $sales2 = User::factory()->create()->assignRole(UserRole::Sales->value);
    $customer = Customer::factory()->create();

    $quotation = Quotation::factory()->create([
        'customer_id' => $customer->id,
        'assigned_to' => $sales1->id,
    ]);

    expect(Gate::forUser($sales1)->allows('update', $quotation))->toBeTrue()
        ->and(Gate::forUser($sales2)->allows('update', $quotation))->toBeFalse();
});
