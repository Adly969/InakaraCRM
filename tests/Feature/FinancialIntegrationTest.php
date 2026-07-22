<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\EventSchemaRegistry;
use App\Models\JournalEntry;
use App\Models\Ledger;
use App\Models\PostingRuleVersion;
use App\Models\SalesEventOutbox;
use App\Models\User;
use App\Services\AccountingGateway;
use App\Services\PostingRuleSimulator;
use App\Services\PostingRuleValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FinancialIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Ledger $ledger;

    protected ChartOfAccount $debitAccount;

    protected ChartOfAccount $creditAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'company_id' => 1,
            'branch_id' => 1,
        ]);

        $this->ledger = Ledger::create([
            'company_id' => 1,
            'code' => 'LEADING',
            'name' => 'Leading Ledger',
            'is_leading' => true,
        ]);

        $this->debitAccount = ChartOfAccount::create([
            'company_id' => 1,
            'branch_id' => 1,
            'account_code' => '1110',
            'name' => 'Cash in Hand',
            'account_type' => 'asset',
            'normal_balance' => 'debit',
            'is_control_account' => false,
            'is_posting_allowed' => true,
        ]);

        $this->creditAccount = ChartOfAccount::create([
            'company_id' => 1,
            'branch_id' => 1,
            'account_code' => '4110',
            'name' => 'Sales Revenue',
            'account_type' => 'revenue',
            'normal_balance' => 'credit',
            'is_control_account' => false,
            'is_posting_allowed' => true,
        ]);
    }

    public function test_dynamic_posting_rule_checks_priorities(): void
    {
        // 1. Setup low priority rule
        PostingRuleVersion::create([
            'company_id' => 1,
            'branch_id' => 1,
            'event_type' => 'SalesInvoiceApproved',
            'version' => 1,
            'priority' => 10,
            'status' => 'PUBLISHED',
            'debit_account_id' => $this->debitAccount->id,
            'credit_account_id' => $this->creditAccount->id,
            'effective_from' => now()->subDay(),
        ]);

        // 2. Setup high priority rule override
        $overrideCredit = ChartOfAccount::create([
            'company_id' => 1,
            'branch_id' => 1,
            'account_code' => '4120',
            'name' => 'VIP Sales Revenue',
            'account_type' => 'revenue',
            'normal_balance' => 'credit',
            'is_control_account' => false,
            'is_posting_allowed' => true,
        ]);

        PostingRuleVersion::create([
            'company_id' => 1,
            'branch_id' => 1,
            'event_type' => 'SalesInvoiceApproved',
            'version' => 2,
            'priority' => 100, // Higher priority wins!
            'status' => 'PUBLISHED',
            'debit_account_id' => $this->debitAccount->id,
            'credit_account_id' => $overrideCredit->id,
            'effective_from' => now()->subDay(),
        ]);

        $gateway = app(AccountingGateway::class);

        $receipt = $gateway->postEvent('SalesInvoiceApproved', [
            'company_id' => 1,
            'branch_id' => 1,
            'amount' => 50000.00,
            'user_id' => $this->user->id,
            'ledger_id' => $this->ledger->id,
        ], 'key_unique_123');

        $this->assertEquals('POSTED', $receipt->status);
        $this->assertDatabaseHas('journal_lines', [
            'account_id' => $overrideCredit->id,
            'credit_amount' => 50000.00,
        ]); // Verifies highest priority resolved override is correctly selected
    }

    public function test_posting_rule_validation_traps_self_posting(): void
    {
        $rule = new PostingRuleVersion([
            'company_id' => 1,
            'branch_id' => 1,
            'event_type' => 'SelfPostEvent',
            'debit_account_id' => $this->debitAccount->id,
            'credit_account_id' => $this->debitAccount->id, // Identical mapping
            'status' => 'DRAFT',
        ]);

        $validator = app(PostingRuleValidator::class);

        $this->expectException(ValidationException::class);
        $validator->validate($rule);
    }

    public function test_idempotency_blocks_duplicate_submission(): void
    {
        PostingRuleVersion::create([
            'company_id' => 1,
            'branch_id' => 1,
            'event_type' => 'SalesInvoiceApproved',
            'version' => 1,
            'priority' => 10,
            'status' => 'PUBLISHED',
            'debit_account_id' => $this->debitAccount->id,
            'credit_account_id' => $this->creditAccount->id,
            'effective_from' => now()->subDay(),
        ]);

        $gateway = app(AccountingGateway::class);

        // First post
        $gateway->postEvent('SalesInvoiceApproved', [
            'company_id' => 1,
            'branch_id' => 1,
            'amount' => 1000.00,
            'user_id' => $this->user->id,
            'ledger_id' => $this->ledger->id,
        ], 'idem_key_abc');

        // Second duplicate post
        $this->expectException(ValidationException::class);
        $gateway->postEvent('SalesInvoiceApproved', [
            'company_id' => 1,
            'branch_id' => 1,
            'amount' => 1000.00,
            'user_id' => $this->user->id,
            'ledger_id' => $this->ledger->id,
        ], 'idem_key_abc');
    }

    public function test_schema_registry_enforces_required_fields(): void
    {
        EventSchemaRegistry::create([
            'schema_name' => 'SalesInvoiceApproved',
            'schema_version' => 1,
            'required_fields' => ['customer_id', 'invoice_reference'],
            'optional_fields' => [],
            'payload_hash' => 'hash123',
            'validation_rules' => [],
            'is_active' => true,
        ]);

        PostingRuleVersion::create([
            'company_id' => 1,
            'branch_id' => 1,
            'event_type' => 'SalesInvoiceApproved',
            'version' => 1,
            'priority' => 10,
            'status' => 'PUBLISHED',
            'debit_account_id' => $this->debitAccount->id,
            'credit_account_id' => $this->creditAccount->id,
            'effective_from' => now()->subDay(),
        ]);

        $gateway = app(AccountingGateway::class);

        // Missing 'invoice_reference' in payload
        $this->expectException(ValidationException::class);
        $gateway->postEvent('SalesInvoiceApproved', [
            'company_id' => 1,
            'branch_id' => 1,
            'amount' => 2000.00,
            'user_id' => $this->user->id,
            'ledger_id' => $this->ledger->id,
            'schema_version' => 1,
            'customer_id' => 101,
        ], 'key_uuid_777');
    }

    public function test_posting_simulation_runs_dry(): void
    {
        PostingRuleVersion::create([
            'company_id' => 1,
            'branch_id' => 1,
            'event_type' => 'SalesInvoiceApproved',
            'version' => 1,
            'priority' => 10,
            'status' => 'PUBLISHED',
            'debit_account_id' => $this->debitAccount->id,
            'credit_account_id' => $this->creditAccount->id,
            'effective_from' => now()->subDay(),
        ]);

        $simulator = app(PostingRuleSimulator::class);

        $result = $simulator->simulate('SalesInvoiceApproved', [
            'company_id' => 1,
            'branch_id' => 1,
            'amount' => 15000.00,
            'user_id' => $this->user->id,
            'ledger_id' => $this->ledger->id,
        ]);

        $this->assertEquals('SalesInvoiceApproved', $result['event_type']);
        $this->assertEquals($this->debitAccount->id, $result['debit_account_id']);
        $this->assertCount(2, $result['lines']);

        // Verify no journal entries actually committed to DB
        $this->assertEquals(0, JournalEntry::count());
    }

    public function test_outbox_processing_dispatches_to_gateway(): void
    {
        // 1. Register Schema for testing
        EventSchemaRegistry::create([
            'schema_name' => 'SalesInvoiceApproved',
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
            'event_type' => 'SalesInvoiceApproved',
            'version' => 1,
            'priority' => 10,
            'status' => 'PUBLISHED',
            'debit_account_id' => $this->debitAccount->id,
            'credit_account_id' => $this->creditAccount->id,
            'effective_from' => now()->subDay(),
        ]);

        // 3. Create outbox record
        SalesEventOutbox::create([
            'event_id' => 'evt-test-123',
            'company_id' => 1,
            'event_type' => 'SalesInvoiceApproved',
            'payload' => [
                'company_id' => 1,
                'branch_id' => 1,
                'amount' => 5000.00,
                'user_id' => $this->user->id,
                'ledger_id' => $this->ledger->id,
                'customer_id' => 101,
                'invoice_reference' => 'INV-001',
                'schema_version' => 1,
            ],
            'correlation_id' => 'corr-123',
            'causation_id' => 'caus-123',
            'trace_id' => 'trace-123',
            'idempotency_key' => 'idem-outbox-999',
            'is_dispatched' => false,
        ]);

        // 4. Run the Artisan command
        $exitCode = Artisan::call('sales:outbox:process');

        $this->assertEquals(0, $exitCode);
        $this->assertTrue(SalesEventOutbox::where('event_id', 'evt-test-123')->first()->is_dispatched);

        // Verify journal entry successfully posted to GL
        $this->assertEquals(1, JournalEntry::count());
    }
}
