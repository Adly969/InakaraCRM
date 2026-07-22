<?php

use App\Enums\CustomerStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use App\Models\CustomerContract;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesOrder;
use App\Models\User;
use App\Services\CreditLimitValidator;
use App\Services\CustomerService;
use App\Services\SalesOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it creates sales order and items, generates ref no, and calculates correct totals', function () {
    $creator = User::factory()->create();
    $customer = Customer::factory()->create();

    $customerService = new CustomerService;
    $service = new SalesOrderService($customerService);

    $data = [
        'customer_id' => $customer->id,
        'subject' => 'SO Pricing Test',
        'currency' => 'IDR',
        'tax_rate' => 11.00,
        'status' => SalesOrderStatus::Draft,
        'items' => [
            [
                'description' => 'Item A',
                'quantity' => 2,
                'unit' => 'pcs',
                'unit_price' => 100000.00,
            ],
            [
                'description' => 'Item B',
                'quantity' => 5,
                'unit' => 'pcs',
                'unit_price' => 20000.00,
            ],
        ],
    ];

    $salesOrder = $service->create($data, $creator);

    // subtotal = (2 * 100000) + (5 * 20000) = 200000 + 100000 = 300000
    // tax = 300000 * 11% = 33000
    // total = 333000
    expect($salesOrder)->toBeInstanceOf(SalesOrder::class)
        ->and($salesOrder->subject)->toBe('SO Pricing Test')
        ->and($salesOrder->tax_rate)->toEqual(11.00)
        ->and($salesOrder->subtotal)->toEqual(300000.00)
        ->and($salesOrder->tax_amount)->toEqual(33000.00)
        ->and($salesOrder->total_amount)->toEqual(333000.00)
        ->and($salesOrder->reference_no)->toBe('SO-000001')
        ->and($salesOrder->created_by)->toBe($creator->id);

    $this->assertDatabaseHas('sales_orders', [
        'id' => $salesOrder->id,
        'reference_no' => 'SO-000001',
        'total_amount' => 333000.00,
    ]);
});

test('it updates drafts by replacing items and updating totals', function () {
    $creator = User::factory()->create();
    $customer = Customer::factory()->create();

    $customerService = new CustomerService;
    $service = new SalesOrderService($customerService);

    $salesOrder = SalesOrder::create([
        'customer_id' => $customer->id,
        'subject' => 'Original Draft',
        'currency' => 'IDR',
        'tax_rate' => 11.00,
        'status' => SalesOrderStatus::Draft,
        'subtotal' => 1000.00,
        'tax_amount' => 110.00,
        'total_amount' => 1110.00,
    ]);

    $data = [
        'customer_id' => $customer->id,
        'subject' => 'Updated Subject',
        'currency' => 'IDR',
        'tax_rate' => 10.00,
        'items' => [
            [
                'description' => 'New line item',
                'quantity' => 10,
                'unit' => 'pcs',
                'unit_price' => 500.00,
            ],
        ],
    ];

    $updated = $service->update($salesOrder, $data, $creator);

    expect($updated->subject)->toBe('Updated Subject')
        ->and($updated->tax_rate)->toEqual(10.00)
        ->and($updated->subtotal)->toEqual(5000.00)
        ->and($updated->total_amount)->toEqual(5500.00);

    $this->assertDatabaseHas('sales_orders', [
        'id' => $salesOrder->id,
        'subject' => 'Updated Subject',
        'total_amount' => 5500.00,
    ]);
});

test('it blocks edits to confirmed sales orders', function () {
    $creator = User::factory()->create();
    $customer = Customer::factory()->create();

    $customerService = new CustomerService;
    $service = new SalesOrderService($customerService);

    $salesOrder = SalesOrder::create([
        'customer_id' => $customer->id,
        'subject' => 'Confirmed Order',
        'currency' => 'IDR',
        'tax_rate' => 11.00,
        'status' => SalesOrderStatus::Confirmed,
        'subtotal' => 1000.00,
        'tax_amount' => 110.00,
        'total_amount' => 1110.00,
    ]);

    $this->expectException(DomainException::class);
    $service->update($salesOrder, ['subject' => 'Edit attempt'], $creator);
});

test('it converts quotation to sales order and promotes lead to customer', function () {
    $creator = User::factory()->create();

    // Set up lead
    $lead = Lead::create([
        'name' => 'John Doe',
        'company_name' => 'Fabulous Furniture',
        'email' => 'john@fabfurniture.com',
        'phone' => '08123456789',
        'source' => 'referral',
        'status' => 'contacted',
        'created_by' => $creator->id,
    ]);

    // Set up quotation
    $quotation = Quotation::create([
        'lead_id' => $lead->id,
        'subject' => 'Convert Deal Test',
        'status' => QuotationStatus::Draft,
        'valid_until' => '2026-12-31',
        'currency' => 'IDR',
        'tax_rate' => 11.00,
        'subtotal' => 200000.00,
        'tax_amount' => 22000.00,
        'total_amount' => 222000.00,
        'created_by' => $creator->id,
    ]);

    $item = new QuotationItem([
        'description' => 'Table Set',
        'quantity' => 1,
        'unit' => 'set',
        'unit_price' => 200000.00,
        'total_price' => 200000.00,
    ]);
    $quotation->items()->save($item);

    $customerService = new CustomerService;
    $service = new SalesOrderService($customerService);

    $salesOrder = $service->createFromQuotation($quotation, $creator);

    expect($salesOrder)->toBeInstanceOf(SalesOrder::class)
        ->and($salesOrder->status)->toBe(SalesOrderStatus::Draft)
        ->and($salesOrder->total_amount)->toEqual(222000.00);

    // Verify lead promoted to customer
    $this->assertDatabaseHas('customers', [
        'name' => 'John Doe',
        'company_name' => 'Fabulous Furniture',
        'email' => 'john@fabfurniture.com',
        'type' => 'organization',
    ]);

    $promotedCustomer = Customer::where('email', 'john@fabfurniture.com')->first();
    expect($salesOrder->customer_id)->toBe($promotedCustomer->id);

    // Verify lead marked as qualified
    expect($lead->fresh()->status->value)->toBe('qualified');

    // Verify quotation status accepted
    expect($quotation->fresh()->status->value)->toBe('accepted');
});

test('it enforces credit limit checking and handles manual hold release', function () {
    $creator = User::factory()->create();
    $customer = Customer::factory()->create(['status' => CustomerStatus::Active]);

    // Create a Customer Contract with specific credit limit override
    CustomerContract::create([
        'company_id' => 1,
        'branch_id' => 1,
        'customer_id' => $customer->id,
        'contract_number' => 'CON-001',
        'status' => 'ACTIVE',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addDay()->toDateString(),
        'credit_limit_override' => 10000.00,
        'version' => 1,
    ]);

    $salesOrder = SalesOrder::create([
        'company_id' => 1,
        'branch_id' => 1,
        'customer_id' => $customer->id,
        'subject' => 'Credit Hold Test',
        'status' => SalesOrderStatus::Draft,
        'total_amount' => 15000.00, // exceeds contract credit limit override
    ]);

    $validator = app(CreditLimitValidator::class);

    // Act
    $isAllowed = $validator->validateAndApplyHold($salesOrder);

    expect($isAllowed)->toBeFalse()
        ->and($salesOrder->fresh()->credit_hold_status)->toBe('hold');

    // Override hold
    $validator->releaseHold($salesOrder, $creator, 'Approved by Finance Director override');

    expect($salesOrder->fresh()->credit_hold_status)->toBe('released')
        ->and($salesOrder->fresh()->credit_hold_override_reason)->toBe('Approved by Finance Director override')
        ->and($salesOrder->fresh()->credit_hold_released_by)->toBe($creator->id);
});
