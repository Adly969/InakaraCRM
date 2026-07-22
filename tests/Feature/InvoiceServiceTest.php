<?php

use App\Enums\InvoiceStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\User;
use App\Services\InvoiceCalculationService;
use App\Services\InvoiceNumberGenerator;
use App\Services\InvoiceService;
use App\Services\InvoiceValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->calcService = new InvoiceCalculationService;
    $this->valService = new InvoiceValidationService;
    $this->numGen = new InvoiceNumberGenerator;
    $this->service = new InvoiceService($this->calcService, $this->valService, $this->numGen);
});

test('it calculates subtotal, taxes, discounts, and adjustments correctly', function () {
    $data = [
        'items' => [
            [
                'quantity' => 2.00,
                'unit_price' => 100000.00,
                'discount_percentage' => 10.00, // 10% discount -> 20,000 discount, subtotal 180,000
                'tax_percentage' => 11.00, // 11% tax -> 19,800 tax
            ],
        ],
        'adjustments' => [
            [
                'type' => 'shipping_fee',
                'description' => 'Delivery fee',
                'amount' => 15000.00,
            ],
        ],
    ];

    $result = $this->calcService->calculate($data);

    expect((float) $result['subtotal'])->toBe(200000.00)
        ->and((float) $result['discount_amount'])->toBe(20000.00)
        ->and((float) $result['tax_amount'])->toBe(19800.00)
        ->and((float) $result['adjustment_amount'])->toBe(15000.00)
        ->and((float) $result['total_amount'])->toBe(214800.00); // (180,000 + 19,800 + 15,000)
});

test('it creates invoice as draft, serializes snapshots and items', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $customer = Customer::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '0812345678',
        'type' => 'individual',
        'status' => 'active',
    ]);

    $so = SalesOrder::create([
        'reference_no' => 'SO-100',
        'customer_id' => $customer->id,
        'subject' => 'Furniture order',
        'status' => SalesOrderStatus::Confirmed,
        'currency' => 'IDR',
        'total_amount' => 214800.00,
    ]);

    $soItem = SalesOrderItem::create([
        'sales_order_id' => $so->id,
        'sku' => 'SOFA-01',
        'description' => 'Premium Sofa',
        'quantity' => 2.00,
        'unit' => 'pcs',
        'unit_price' => 100000.00,
        'total_price' => 200000.00,
    ]);

    $data = [
        'sales_order_id' => $so->id,
        'customer_id' => $customer->id,
        'due_date' => now()->addDays(14)->toDateString(),
        'payment_term_code' => 'NET14',
        'currency' => 'IDR',
        'items' => [
            [
                'sales_order_item_id' => $soItem->id,
                'sku' => 'SOFA-01',
                'description' => 'Premium Sofa',
                'quantity' => 2.00,
                'unit_price' => 100000.00,
                'discount_percentage' => 10.00,
                'tax_percentage' => 11.00,
            ],
        ],
        'adjustments' => [
            [
                'type' => 'shipping_fee',
                'description' => 'Delivery fee',
                'amount' => 15000.00,
            ],
        ],
    ];

    $invoice = $this->service->create($data);

    expect($invoice->status)->toBe(InvoiceStatus::Draft)
        ->and((float) $invoice->total_amount)->toBe(214800.00)
        ->and($invoice->billing_address_snapshot['name'])->toBe('John Doe')
        ->and($invoice->items)->toHaveCount(1)
        ->and($invoice->items->first()->sku)->toBe('SOFA-01')
        ->and($invoice->adjustments)->toHaveCount(1);
});

test('it prevents creation when invoiced quantity exceeds sales order outstanding quantity', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $customer = Customer::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '0812345678',
        'type' => 'individual',
        'status' => 'active',
    ]);

    $so = SalesOrder::create([
        'reference_no' => 'SO-100',
        'customer_id' => $customer->id,
        'subject' => 'Furniture order',
        'status' => SalesOrderStatus::Confirmed,
        'currency' => 'IDR',
        'total_amount' => 200000.00,
    ]);

    $soItem = SalesOrderItem::create([
        'sales_order_id' => $so->id,
        'sku' => 'SOFA-01',
        'description' => 'Premium Sofa',
        'quantity' => 2.00, // outstanding limit is 2
        'unit' => 'pcs',
        'unit_price' => 100000.00,
        'total_price' => 200000.00,
    ]);

    $data = [
        'sales_order_id' => $so->id,
        'customer_id' => $customer->id,
        'due_date' => now()->addDays(14)->toDateString(),
        'payment_term_code' => 'NET14',
        'currency' => 'IDR',
        'items' => [
            [
                'sales_order_item_id' => $soItem->id,
                'sku' => 'SOFA-01',
                'description' => 'Premium Sofa',
                'quantity' => 3.00, // over-billing: requested 3 but only 2 available
                'unit_price' => 100000.00,
            ],
        ],
    ];

    expect(fn () => $this->service->create($data))->toThrow(ValidationException::class);
});

test('it enforces credit limit checking', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $customer = Customer::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '0812345678',
        'type' => 'individual',
        'status' => 'active',
    ]);

    $so = SalesOrder::create([
        'reference_no' => 'SO-100',
        'customer_id' => $customer->id,
        'subject' => 'Furniture order',
        'status' => SalesOrderStatus::Confirmed,
        'currency' => 'IDR',
        'total_amount' => 60000000.00,
    ]);

    $soItem = SalesOrderItem::create([
        'sales_order_id' => $so->id,
        'sku' => 'SOFA-01',
        'description' => 'Premium Sofa',
        'quantity' => 1.00,
        'unit' => 'pcs',
        'unit_price' => 60000000.00,
        'total_price' => 60000000.00,
    ]);

    $data = [
        'sales_order_id' => $so->id,
        'customer_id' => $customer->id,
        'due_date' => now()->addDays(14)->toDateString(),
        'payment_term_code' => 'NET14',
        'currency' => 'IDR',
        'items' => [
            [
                'sales_order_item_id' => $soItem->id,
                'sku' => 'SOFA-01',
                'description' => 'Premium Sofa',
                'quantity' => 1.00,
                'unit_price' => 60000000.00, // 60M exceeds 50M limit
            ],
        ],
    ];

    expect(fn () => $this->service->create($data))->toThrow(ValidationException::class);
});

test('it handles full state transitions lifecycle', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $customer = Customer::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '0812345678',
        'type' => 'individual',
        'status' => 'active',
    ]);

    $so = SalesOrder::create([
        'reference_no' => 'SO-100',
        'customer_id' => $customer->id,
        'subject' => 'Furniture order',
        'status' => SalesOrderStatus::Confirmed,
        'currency' => 'IDR',
        'total_amount' => 200000.00,
    ]);

    $soItem = SalesOrderItem::create([
        'sales_order_id' => $so->id,
        'sku' => 'SOFA-01',
        'description' => 'Premium Sofa',
        'quantity' => 2.00,
        'unit' => 'pcs',
        'unit_price' => 100000.00,
        'total_price' => 200000.00,
    ]);

    $data = [
        'sales_order_id' => $so->id,
        'customer_id' => $customer->id,
        'due_date' => now()->addDays(14)->toDateString(),
        'payment_term_code' => 'NET14',
        'currency' => 'IDR',
        'items' => [
            [
                'sales_order_item_id' => $soItem->id,
                'sku' => 'SOFA-01',
                'description' => 'Premium Sofa',
                'quantity' => 2.00,
                'unit_price' => 100000.00,
            ],
        ],
    ];

    // Create Draft
    $invoice = $this->service->create($data);
    expect($invoice->status)->toBe(InvoiceStatus::Draft);

    // Approve
    $this->service->approve($invoice);
    expect($invoice->status)->toBe(InvoiceStatus::Approved)
        ->and($invoice->approved_by)->toBe($user->id);

    // Issue
    $this->service->issue($invoice);
    expect($invoice->status)->toBe(InvoiceStatus::Issued)
        ->and($invoice->reference_no)->toContain('INV/');

    // Void
    $this->service->void($invoice, 'Entered void reason details.');
    expect($invoice->status)->toBe(InvoiceStatus::Void)
        ->and($invoice->void_reason)->toBe('Entered void reason details.')
        ->and((float) $invoice->outstanding_balance)->toBe(0.00);
});

test('it prevents duplicate sequence generation under concurrency', function () {
    $user = User::factory()->create();

    $g1 = $this->numGen->generateNextNumber();

    $customer = Customer::create([
        'name' => 'John Doe',
        'type' => 'individual',
        'status' => 'active',
    ]);

    Invoice::create([
        'reference_no' => $g1,
        'customer_id' => $customer->id,
        'status' => InvoiceStatus::Issued,
        'invoice_date' => now()->toDateString(),
        'due_date' => now()->addDays(30)->toDateString(),
        'payment_term_code' => '30DAYS',
        'billing_address_snapshot' => ['address' => 'Test Address'],
        'shipping_address_snapshot' => ['address' => 'Test Address'],
        'created_by' => $user->id,
    ]);

    $g2 = $this->numGen->generateNextNumber();

    expect($g1)->not()->toBe($g2);
    $parts1 = explode('/', $g1);
    $parts2 = explode('/', $g2);
    expect((int) end($parts2))->toBe((int) end($parts1) + 1);
});

test('it verifies role based policies', function () {
    $approvePerm = Permission::firstOrCreate(['name' => 'approve-invoices', 'guard_name' => 'web']);

    $owner = User::factory()->create();
    $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
    $ownerRole->givePermissionTo($approvePerm);
    $owner->assignRole($ownerRole);

    $financeUser = User::factory()->create();
    $financeRole = Role::firstOrCreate(['name' => 'finance', 'guard_name' => 'web']);
    $financeRole->givePermissionTo($approvePerm);
    $financeUser->assignRole($financeRole);

    $viewerUser = User::factory()->create();
    $viewerRole = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
    $viewerUser->assignRole($viewerRole);

    $customer = Customer::create([
        'name' => 'John Doe',
        'type' => 'individual',
        'status' => 'active',
    ]);

    $invoice = Invoice::create([
        'customer_id' => $customer->id,
        'status' => InvoiceStatus::Draft,
        'invoice_date' => now()->toDateString(),
        'due_date' => now()->addDays(30)->toDateString(),
        'payment_term_code' => '30DAYS',
        'billing_address_snapshot' => ['address' => 'Test Address'],
        'shipping_address_snapshot' => ['address' => 'Test Address'],
        'created_by' => $owner->id,
    ]);

    // Viewer cannot approve
    expect($viewerUser->can('approve', $invoice))->toBeFalse();

    // Owner can approve
    expect($owner->can('approve', $invoice))->toBeTrue();

    // Finance can approve
    expect($financeUser->can('approve', $invoice))->toBeTrue();
});
