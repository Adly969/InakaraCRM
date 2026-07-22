<?php

use App\Enums\QuotationStatus;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use App\Services\QuotationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it creates quotation and items, generates ref no, and calculates correct totals', function () {
    $creator = User::factory()->create();
    $customer = Customer::factory()->create();
    $service = new QuotationService;

    $data = [
        'customer_id' => $customer->id,
        'subject' => 'Service Pricing Test',
        'valid_until' => '2026-08-31',
        'currency' => 'USD',
        'tax_rate' => 10.00,
        'status' => QuotationStatus::Draft,
        'items' => [
            [
                'description' => 'Product A',
                'quantity' => 5,
                'unit' => 'pcs',
                'unit_price' => 100.00,
            ],
            [
                'description' => 'Product B',
                'quantity' => 1.5,
                'unit' => 'hours',
                'unit_price' => 200.00,
            ],
        ],
    ];

    $quotation = $service->create($data, $creator);

    // subtotal = (5 * 100) + (1.5 * 200) = 500 + 300 = 800
    // tax = 800 * 10% = 80
    // total = 880
    expect($quotation)->toBeInstanceOf(Quotation::class)
        ->and($quotation->subject)->toBe('Service Pricing Test')
        ->and($quotation->tax_rate)->toEqual(10.00)
        ->and($quotation->subtotal)->toEqual(800.00)
        ->and($quotation->tax_amount)->toEqual(80.00)
        ->and($quotation->total_amount)->toEqual(880.00)
        ->and($quotation->reference_no)->toBe('QT-000001')
        ->and($quotation->created_by)->toBe($creator->id);

    $this->assertDatabaseHas('quotations', [
        'id' => $quotation->id,
        'reference_no' => 'QT-000001',
        'tax_rate' => 10.00,
        'total_amount' => 880.00,
    ]);

    $this->assertDatabaseHas('quotation_items', [
        'quotation_id' => $quotation->id,
        'description' => 'Product B',
        'total_price' => 300.00,
    ]);
});

test('it replaces items list and recalculates totals on update', function () {
    $creator = User::factory()->create();
    $customer = Customer::factory()->create();
    $service = new QuotationService;

    $quotation = Quotation::factory()->create([
        'customer_id' => $customer->id,
        'status' => QuotationStatus::Draft,
    ]);
    $item = QuotationItem::factory()->create([
        'quotation_id' => $quotation->id,
        'description' => 'Old Line',
    ]);

    $data = [
        'customer_id' => $customer->id,
        'subject' => 'Updated Subject',
        'valid_until' => '2026-08-31',
        'currency' => 'USD',
        'tax_rate' => 11.00,
        'items' => [
            [
                'description' => 'New Line C',
                'quantity' => 10,
                'unit' => 'pcs',
                'unit_price' => 5.00,
            ],
        ],
    ];

    $updated = $service->update($quotation, $data, $creator);

    expect($updated->subject)->toBe('Updated Subject')
        ->and($updated->subtotal)->toEqual(50.00)
        ->and($updated->total_amount)->toEqual(55.50);

    $this->assertDatabaseMissing('quotation_items', ['id' => $item->id]);
    $this->assertDatabaseHas('quotation_items', [
        'quotation_id' => $quotation->id,
        'description' => 'New Line C',
        'total_price' => 50.00,
    ]);
});

test('it throws exception when updating details of non-draft quotation', function () {
    $creator = User::factory()->create();
    $customer = Customer::factory()->create();
    $service = new QuotationService;

    $quotation = Quotation::factory()->create([
        'customer_id' => $customer->id,
        'status' => QuotationStatus::Sent,
    ]);

    $data = [
        'customer_id' => $customer->id,
        'subject' => 'Hacker Try Update Subject',
        'valid_until' => '2026-08-31',
        'currency' => 'USD',
        'items' => [],
    ];

    $this->expectException(DomainException::class);
    $service->update($quotation, $data, $creator);
});

test('it throws exception on invalid status transitions', function () {
    $creator = User::factory()->create();
    $customer = Customer::factory()->create();
    $service = new QuotationService;

    $quotation = Quotation::factory()->create([
        'customer_id' => $customer->id,
        'status' => QuotationStatus::Accepted,
    ]);

    $data = [
        'status' => QuotationStatus::Sent->value,
    ];

    $this->expectException(DomainException::class);
    $service->update($quotation, $data, $creator);
});
