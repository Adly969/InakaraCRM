<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * @property User $supervisor
 * @property User $manager
 * @property User $director
 * @property User $clerk
 * @property PaymentPostingService $service
 */
class PaymentPostingServiceTest extends TestCase {}

/** @var PaymentPostingServiceTest $this */

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethodType;
use App\Enums\PaymentStatus;
use App\Exceptions\InvalidPaymentTransitionException;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\OfficialReceipt;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\User;
use App\Services\PaymentPostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed roles and permissions
    $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);

    $this->supervisor = User::factory()->create();
    $this->supervisor->assignRole('finance'); // has approve-payments-l1

    $this->manager = User::factory()->create();
    $this->manager->assignRole('manager'); // has approve-payments-l2

    $this->director = User::factory()->create();
    $this->director->assignRole('owner'); // has approve-payments-l3

    $this->clerk = User::factory()->create();
    $this->clerk->assignRole('finance'); // has create, submit, verify

    $this->service = app(PaymentPostingService::class);
});

test('it creates payment draft and computes unallocated balances', function () {
    $this->actingAs($this->clerk);

    $customer = Customer::create([
        'name' => 'John Doe',
        'type' => 'retail',
        'status' => 'active',
        'created_by' => $this->clerk->id,
    ]);

    $invoice = Invoice::create([
        'customer_id' => $customer->id,
        'status' => InvoiceStatus::Issued,
        'invoice_date' => now()->toDateString(),
        'due_date' => now()->addDays(30)->toDateString(),
        'payment_term_code' => 'NET30',
        'total_amount' => 100000.00,
        'outstanding_balance' => 100000.00,
        'billing_address_snapshot' => ['name' => 'John', 'address' => 'Test'],
        'shipping_address_snapshot' => ['name' => 'John', 'address' => 'Test'],
        'created_by' => $this->clerk->id,
    ]);

    $data = [
        'customer_id' => $customer->id,
        'payment_date' => now()->toDateString(),
        'payment_method' => PaymentMethodType::Cash->value,
        'amount' => 150000.00,
        'currency' => 'IDR',
        'allocations' => [
            [
                'invoice_id' => $invoice->id,
                'amount' => 100000.00,
                'notes' => 'Full allocation',
            ],
        ],
    ];

    $payment = $this->service->create($data);

    expect($payment->status)->toBe(PaymentStatus::Draft)
        ->and((float) $payment->amount)->toBe(150000.00)
        ->and((float) $payment->allocated_amount)->toBe(100000.00)
        ->and((float) $payment->unallocated_amount)->toBe(50000.00)
        ->and(PaymentAllocation::where('payment_id', $payment->id)->count())->toBe(1);
});

test('it transitions payment status and enforces approval tiers', function () {
    $customer = Customer::create([
        'name' => 'John Doe',
        'type' => 'retail',
        'status' => 'active',
        'created_by' => $this->clerk->id,
    ]);

    $invoice = Invoice::create([
        'customer_id' => $customer->id,
        'status' => InvoiceStatus::Issued,
        'invoice_date' => now()->toDateString(),
        'due_date' => now()->addDays(30)->toDateString(),
        'payment_term_code' => 'NET30',
        'total_amount' => 20000.00,
        'outstanding_balance' => 20000.00,
        'billing_address_snapshot' => ['name' => 'John', 'address' => 'Test'],
        'shipping_address_snapshot' => ['name' => 'John', 'address' => 'Test'],
        'created_by' => $this->clerk->id,
    ]);

    // Tier 1 test (Amount < 50M IDR)
    $payment = Payment::create([
        'customer_id' => $customer->id,
        'payment_date' => now()->toDateString(),
        'payment_method' => PaymentMethodType::Cash->value,
        'amount' => 20000.00,
        'allocated_amount' => 20000.00,
        'unallocated_amount' => 0.00,
        'currency' => 'IDR',
        'status' => PaymentStatus::Draft->value,
        'created_by' => $this->clerk->id,
    ]);

    PaymentAllocation::create([
        'payment_id' => $payment->id,
        'invoice_id' => $invoice->id,
        'amount' => 20000.00,
    ]);

    // 1. Submit
    $this->actingAs($this->clerk);
    $this->service->submit($payment);
    expect($payment->fresh()->status)->toBe(PaymentStatus::Submitted);

    // 2. Verify
    $this->service->verify($payment);
    expect($payment->fresh()->status)->toBe(PaymentStatus::Verified);

    // 3. Approve L1 (Four-eyes check prevents clerk approval)
    $this->actingAs($this->clerk);
    expect(fn () => $this->service->approve($payment))->toThrow(InvalidPaymentTransitionException::class);

    $this->actingAs($this->supervisor);
    $this->service->approve($payment);
    expect($payment->fresh()->status)->toBe(PaymentStatus::Approved);

    // 4. Post
    $this->service->post($payment);
    expect($payment->fresh()->status)->toBe(PaymentStatus::Posted)
        ->and((float) $invoice->fresh()->outstanding_balance)->toBe(0.00)
        ->and(OfficialReceipt::where('payment_id', $payment->id)->count())->toBe(1);
});
