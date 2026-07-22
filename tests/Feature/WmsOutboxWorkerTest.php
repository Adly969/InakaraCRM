<?php

use App\Models\ChartOfAccount;
use App\Models\EventSchemaRegistry;
use App\Models\JournalEntry;
use App\Models\Ledger;
use App\Models\PostingRuleVersion;
use App\Models\SalesEventOutbox;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

test('it dispatches WMS inventory outbox events to the Accounting Gateway and posts to General Ledger', function () {
    $user = User::factory()->create([
        'company_id' => 1,
        'branch_id' => 1,
    ]);

    $ledger = Ledger::create([
        'company_id' => 1,
        'code' => 'LEADING',
        'name' => 'Leading Ledger',
        'is_leading' => true,
    ]);

    $debitAccount = ChartOfAccount::create([
        'company_id' => 1,
        'branch_id' => 1,
        'account_code' => '1110',
        'name' => 'Cash in Hand',
        'account_type' => 'asset',
        'normal_balance' => 'debit',
        'is_control_account' => false,
        'is_posting_allowed' => true,
    ]);

    $creditAccount = ChartOfAccount::create([
        'company_id' => 1,
        'branch_id' => 1,
        'account_code' => '1200',
        'name' => 'Inventory Asset',
        'account_type' => 'asset',
        'normal_balance' => 'debit',
        'is_control_account' => false,
        'is_posting_allowed' => true,
    ]);

    // 1. Register Schema for testing
    EventSchemaRegistry::create([
        'schema_name' => 'InventoryReceived',
        'schema_version' => 1,
        'required_fields' => ['customer_id', 'invoice_reference'],
        'optional_fields' => [],
        'payload_hash' => 'hash123',
        'validation_rules' => [],
        'is_active' => true,
    ]);

    // 2. Create posting rule mapping
    PostingRuleVersion::create([
        'company_id' => 1,
        'branch_id' => 1,
        'event_type' => 'InventoryReceived',
        'version' => 1,
        'priority' => 10,
        'status' => 'PUBLISHED',
        'debit_account_id' => $debitAccount->id,
        'credit_account_id' => $creditAccount->id,
        'effective_from' => now()->subDay(),
    ]);

    // 3. Create outbox record
    SalesEventOutbox::create([
        'event_id' => 'evt-wms-777',
        'company_id' => 1,
        'event_type' => 'InventoryReceived',
        'payload' => [
            'company_id' => 1,
            'branch_id' => 1,
            'amount' => 8000.00,
            'user_id' => $user->id,
            'ledger_id' => $ledger->id,
            'customer_id' => 102,
            'invoice_reference' => 'PO-001-REC',
            'schema_version' => 1,
        ],
        'correlation_id' => 'corr-wms-777',
        'causation_id' => 'caus-wms-777',
        'trace_id' => 'trace-wms-777',
        'idempotency_key' => 'idem-wms-outbox-999',
        'is_dispatched' => false,
    ]);

    // 4. Run the Artisan command
    $exitCode = Artisan::call('wms:outbox:process');

    expect($exitCode)->toBe(0)
        ->and(SalesEventOutbox::where('event_id', 'evt-wms-777')->first()->is_dispatched)->toBeTrue();

    // Verify journal entry successfully posted to GL via Gateway
    expect(JournalEntry::count())->toBe(1);
});
