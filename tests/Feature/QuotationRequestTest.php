<?php

use App\Enums\QuotationStatus;
use App\Http\Requests\StoreQuotationRequest;
use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

test('StoreQuotationRequest validation fails if both customer_id and lead_id are missing', function () {
    $data = [
        'subject' => 'No relation proposal',
        'valid_until' => '2026-08-31',
        'currency' => 'IDR',
        'items' => [
            ['description' => 'Product A', 'quantity' => 1, 'unit' => 'pcs', 'unit_price' => 100],
        ],
    ];

    $request = new StoreQuotationRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->has('customer_id'))->toBeTrue()
        ->and($validator->errors()->has('lead_id'))->toBeTrue();
});

test('StoreQuotationRequest validation fails if both customer_id and lead_id are provided', function () {
    $customer = Customer::factory()->create();
    $lead = Lead::factory()->create();

    $data = [
        'customer_id' => $customer->id,
        'lead_id' => $lead->id,
        'subject' => 'Double relation proposal',
        'valid_until' => '2026-08-31',
        'currency' => 'IDR',
        'items' => [
            ['description' => 'Product A', 'quantity' => 1, 'unit' => 'pcs', 'unit_price' => 100],
        ],
    ];

    $request = new StoreQuotationRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->has('customer_id'))->toBeTrue()
        ->and($validator->errors()->has('lead_id'))->toBeTrue();
});

test('StoreQuotationRequest validation fails if items list is empty', function () {
    $customer = Customer::factory()->create();

    $data = [
        'customer_id' => $customer->id,
        'subject' => 'Empty items proposal',
        'valid_until' => '2026-08-31',
        'currency' => 'IDR',
        'items' => [],
    ];

    $request = new StoreQuotationRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->has('items'))->toBeTrue();
});

test('StoreQuotationRequest validation passes with correct customer association', function () {
    $customer = Customer::factory()->create();

    $data = [
        'customer_id' => $customer->id,
        'subject' => 'Valid proposal',
        'valid_until' => '2026-08-31',
        'currency' => 'IDR',
        'status' => QuotationStatus::Draft->value,
        'items' => [
            ['description' => 'Product A', 'quantity' => 10, 'unit' => 'pcs', 'unit_price' => 2500],
        ],
    ];

    $request = new StoreQuotationRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeTrue();
});
