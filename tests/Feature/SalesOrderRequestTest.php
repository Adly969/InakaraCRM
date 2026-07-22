<?php

use App\Enums\SalesOrderStatus;
use App\Http\Requests\StoreSalesOrderRequest;
use App\Http\Requests\UpdateSalesOrderRequest;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

test('store request validation requires customer and non-empty items array', function () {
    $request = new StoreSalesOrderRequest;

    $rules = $request->rules();

    // Invalid payload
    $validator1 = Validator::make([
        'subject' => 'Invalid SO',
        'currency' => 'IDR',
    ], $rules);

    expect($validator1->fails())->toBeTrue()
        ->and($validator1->errors()->has('customer_id'))->toBeTrue()
        ->and($validator1->errors()->has('items'))->toBeTrue();

    // Valid payload
    $customer = Customer::factory()->create();
    $validator2 = Validator::make([
        'customer_id' => $customer->id,
        'subject' => 'Valid SO',
        'currency' => 'IDR',
        'items' => [
            [
                'description' => 'Product A',
                'quantity' => 1,
                'unit' => 'pcs',
                'unit_price' => 1000,
            ],
        ],
    ], $rules);

    expect($validator2->fails())->toBeFalse();
});

test('update request validation conditionally requires cancellation reason', function () {
    $request = new UpdateSalesOrderRequest;

    $rules = $request->rules();
    $customer = Customer::factory()->create();

    // Cancelled status but missing reason
    $validator1 = Validator::make([
        'customer_id' => $customer->id,
        'subject' => 'Cancelling SO',
        'currency' => 'IDR',
        'status' => SalesOrderStatus::Cancelled->value,
        'items' => [
            [
                'description' => 'Product A',
                'quantity' => 1,
                'unit' => 'pcs',
                'unit_price' => 1000,
            ],
        ],
    ], $rules);

    expect($validator1->fails())->toBeTrue()
        ->and($validator1->errors()->has('cancellation_reason'))->toBeTrue();

    // Cancelled status with valid reason
    $validator2 = Validator::make([
        'customer_id' => $customer->id,
        'subject' => 'Cancelling SO',
        'currency' => 'IDR',
        'status' => SalesOrderStatus::Cancelled->value,
        'cancellation_reason' => 'Client changed their mind',
        'items' => [
            [
                'description' => 'Product A',
                'quantity' => 1,
                'unit' => 'pcs',
                'unit_price' => 1000,
            ],
        ],
    ], $rules);

    expect($validator2->fails())->toBeFalse();
});
