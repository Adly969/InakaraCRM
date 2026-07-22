<?php

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

test('StoreLeadRequest validation passes with valid data', function () {
    $user = User::factory()->create();

    $data = [
        'name' => 'John Doe',
        'company_name' => 'Acme Corp',
        'email' => 'john@example.com',
        'phone' => '123456789',
        'source' => LeadSource::Marketing->value,
        'assigned_to' => $user->id,
    ];

    $request = new StoreLeadRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeTrue();
});

test('StoreLeadRequest validation fails with invalid data', function () {
    $data = [
        'name' => '', // missing name
        'email' => 'invalid-email', // invalid email
        'source' => 'invalid-source', // invalid source enum
        'assigned_to' => 999999, // user does not exist
    ];

    $request = new StoreLeadRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toContain('name', 'email', 'source', 'assigned_to');
});

test('UpdateLeadRequest validation passes with valid data', function () {
    $user = User::factory()->create();

    $data = [
        'name' => 'John Updated',
        'company_name' => 'Acme Corp',
        'email' => 'john@example.com',
        'phone' => '123456789',
        'source' => LeadSource::Marketing->value,
        'status' => LeadStatus::Contacted->value,
        'assigned_to' => $user->id,
    ];

    $request = new UpdateLeadRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeTrue();
});

test('UpdateLeadRequest validation fails when status is disqualified but reason is missing', function () {
    $data = [
        'name' => 'John Updated',
        'source' => LeadSource::Marketing->value,
        'status' => LeadStatus::Disqualified->value,
        // missing disqualification_reason
    ];

    $request = new UpdateLeadRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->has('disqualification_reason'))->toBeTrue();
});

test('UpdateLeadRequest validation passes when status is disqualified and reason is provided', function () {
    $data = [
        'name' => 'John Updated',
        'source' => LeadSource::Marketing->value,
        'status' => LeadStatus::Disqualified->value,
        'disqualification_reason' => 'Lost interest',
    ];

    $request = new UpdateLeadRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeTrue();
});
