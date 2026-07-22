<?php

use App\Http\Requests\StoreProductionOrderRequest;
use App\Http\Requests\UpdateProductionOrderRequest;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

test('store validation rules apply correctly', function () {
    $rules = (new StoreProductionOrderRequest)->rules();

    // Invalid data (missing customer, subject, items)
    $validator = Validator::make([
        'currency' => 'IDR',
    ], $rules);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('customer_id'))->toBeTrue()
        ->and($validator->errors()->has('subject'))->toBeTrue()
        ->and($validator->errors()->has('items'))->toBeTrue();

    // Valid data
    $validator = Validator::make([
        'customer_id' => Customer::factory()->create()->id,
        'subject' => 'Kitchen Set',
        'currency' => 'IDR',
        'items' => [
            [
                'description' => 'Cabinets',
                'quantity' => 10,
                'unit' => 'pcs',
                'unit_price' => 120000,
            ],
        ],
    ], $rules);

    expect($validator->fails())->toBeFalse();
});

test('update validation rules apply transition rules', function () {
    $rules = (new UpdateProductionOrderRequest)->rules();

    // Cancellation reason required when status is cancelled
    $validator = Validator::make([
        'customer_id' => Customer::factory()->create()->id,
        'subject' => 'Kitchen Set',
        'currency' => 'IDR',
        'status' => 'cancelled',
        '_updated_at' => now()->toIso8601String(),
        'items' => [
            [
                'description' => 'Cabinets',
                'quantity' => 10,
                'unit' => 'pcs',
                'unit_price' => 120000,
            ],
        ],
    ], $rules);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('cancellation_reason'))->toBeTrue();
});
