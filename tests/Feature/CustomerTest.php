<?php

use App\Enums\CustomerStatus;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('customer can be created using factory', function () {
    $customer = Customer::factory()->create([
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'type' => 'individual',
    ]);

    expect($customer)->toBeInstanceOf(Customer::class)
        ->name->toBe('Jane Smith')
        ->email->toBe('jane@example.com')
        ->type->toBe('individual')
        ->status->toBe(CustomerStatus::Active);
});

test('customer relationships can be accessed', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create([
        'assigned_to' => $user->id,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    expect($customer->assignedTo)->toBeInstanceOf(User::class)
        ->and($customer->creator)->toBeInstanceOf(User::class)
        ->and($customer->updater)->toBeInstanceOf(User::class)
        ->and($customer->deleter)->toBeNull();

    $customer->deleted_by = $user->id;
    $customer->save();
    $customer->delete();

    expect($customer->fresh()->deleter)->toBeInstanceOf(User::class);
});

test('customer blacklisted state works correctly', function () {
    $customer = Customer::factory()->blacklisted()->create();

    expect($customer->status)->toBe(CustomerStatus::Blacklisted);
});
