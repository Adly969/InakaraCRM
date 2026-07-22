<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * @property User $user
 * @property Customer $customer
 * @property ReceivableAgingService $agingService
 */
class ReceivableAgingServiceTest extends TestCase {}

/** @var ReceivableAgingServiceTest $this */

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use App\Services\ReceivableAgingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->agingService = app(ReceivableAgingService::class);

    $this->customer = Customer::create([
        'name' => 'Acme Corporation',
        'type' => 'retail',
        'status' => 'active',
        'created_by' => $this->user->id,
    ]);
});

test('it categorizes invoices into correct aging buckets', function () {
    // Current invoice (due in 5 days)
    Invoice::create([
        'customer_id' => $this->customer->id,
        'status' => InvoiceStatus::Issued,
        'invoice_date' => Carbon::today()->subDays(10)->toDateString(),
        'due_date' => Carbon::today()->addDays(5)->toDateString(),
        'payment_term_code' => 'NET15',
        'total_amount' => 10000.00,
        'outstanding_balance' => 10000.00,
        'billing_address_snapshot' => ['name' => 'Acme', 'address' => 'Test'],
        'shipping_address_snapshot' => ['name' => 'Acme', 'address' => 'Test'],
        'created_by' => $this->user->id,
    ]);

    // 10 days overdue (due 10 days ago)
    Invoice::create([
        'customer_id' => $this->customer->id,
        'status' => InvoiceStatus::Overdue,
        'invoice_date' => Carbon::today()->subDays(20)->toDateString(),
        'due_date' => Carbon::today()->subDays(10)->toDateString(),
        'payment_term_code' => 'NET10',
        'total_amount' => 20000.00,
        'outstanding_balance' => 20000.00,
        'billing_address_snapshot' => ['name' => 'Acme', 'address' => 'Test'],
        'shipping_address_snapshot' => ['name' => 'Acme', 'address' => 'Test'],
        'created_by' => $this->user->id,
    ]);

    // 40 days overdue (due 40 days ago)
    Invoice::create([
        'customer_id' => $this->customer->id,
        'status' => InvoiceStatus::Overdue,
        'invoice_date' => Carbon::today()->subDays(50)->toDateString(),
        'due_date' => Carbon::today()->subDays(40)->toDateString(),
        'payment_term_code' => 'NET10',
        'total_amount' => 30000.00,
        'outstanding_balance' => 30000.00,
        'billing_address_snapshot' => ['name' => 'Acme', 'address' => 'Test'],
        'shipping_address_snapshot' => ['name' => 'Acme', 'address' => 'Test'],
        'created_by' => $this->user->id,
    ]);

    // 70 days overdue (due 70 days ago)
    Invoice::create([
        'customer_id' => $this->customer->id,
        'status' => InvoiceStatus::Overdue,
        'invoice_date' => Carbon::today()->subDays(80)->toDateString(),
        'due_date' => Carbon::today()->subDays(70)->toDateString(),
        'payment_term_code' => 'NET10',
        'total_amount' => 40000.00,
        'outstanding_balance' => 40000.00,
        'billing_address_snapshot' => ['name' => 'Acme', 'address' => 'Test'],
        'shipping_address_snapshot' => ['name' => 'Acme', 'address' => 'Test'],
        'created_by' => $this->user->id,
    ]);

    // 100 days overdue (due 100 days ago)
    Invoice::create([
        'customer_id' => $this->customer->id,
        'status' => InvoiceStatus::Overdue,
        'invoice_date' => Carbon::today()->subDays(110)->toDateString(),
        'due_date' => Carbon::today()->subDays(100)->toDateString(),
        'payment_term_code' => 'NET10',
        'total_amount' => 50000.00,
        'outstanding_balance' => 50000.00,
        'billing_address_snapshot' => ['name' => 'Acme', 'address' => 'Test'],
        'shipping_address_snapshot' => ['name' => 'Acme', 'address' => 'Test'],
        'created_by' => $this->user->id,
    ]);

    $buckets = $this->agingService->getAgingByCustomer($this->customer->id);

    expect((float) $buckets['current'])->toBe(10000.00)
        ->and((float) $buckets['bucket_1_30'])->toBe(20000.00)
        ->and((float) $buckets['bucket_31_60'])->toBe(30000.00)
        ->and((float) $buckets['bucket_61_90'])->toBe(40000.00)
        ->and((float) $buckets['bucket_over_90'])->toBe(50000.00)
        ->and((float) $buckets['total'])->toBe(150000.00);
});
