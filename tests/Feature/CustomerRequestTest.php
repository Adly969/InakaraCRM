<?php

use App\Enums\CustomerStatus;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

test('StoreCustomerRequest validation passes with valid data', function () {
    $user = User::factory()->create();

    $data = [
        'name' => 'Jane Smith',
        'company_name' => 'Acme Inc',
        'email' => 'jane@acme.com',
        'phone' => '1234567890',
        'type' => 'individual',
        'status' => CustomerStatus::Active->value,
        'assigned_to' => $user->id,
        'notes' => 'Test notes',
    ];

    $request = new StoreCustomerRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeTrue();
});

test('StoreCustomerRequest validation fails with invalid data', function () {
    $data = [
        'name' => '', // missing name
        'email' => 'invalid-email', // invalid email
        'type' => 'invalid-type', // invalid type
        'assigned_to' => 999999, // user does not exist
    ];

    $request = new StoreCustomerRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toContain('name', 'email', 'type', 'assigned_to');
});

test('UpdateCustomerRequest validation passes with valid data', function () {
    $user = User::factory()->create();

    $data = [
        'name' => 'Jane Updated',
        'type' => 'organization',
        'status' => CustomerStatus::Inactive->value,
        'assigned_to' => $user->id,
    ];

    $request = new UpdateCustomerRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeTrue();
});

test('UpdateCustomerRequest validation fails when status is missing', function () {
    $data = [
        'name' => 'Jane Updated',
        'type' => 'organization',
        // missing status
    ];

    $request = new UpdateCustomerRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->has('status'))->toBeTrue();
});
