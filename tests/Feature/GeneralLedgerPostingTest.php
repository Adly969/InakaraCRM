<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\FinancialDimension;
use App\Models\FinancialDimensionValue;
use App\Models\JournalEntry;
use App\Models\Ledger;
use App\Models\PostingRule;
use App\Models\User;
use App\Services\AccountingGateway;
use App\Services\DigitalSignatureService;
use App\Services\PostingEngineService;
use App\Services\ReversalEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class GeneralLedgerPostingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Ledger $ledger;

    protected ChartOfAccount $cashAccount;

    protected ChartOfAccount $revenueAccount;

    protected ChartOfAccount $controlAccount;

    protected PostingEngineService $postingEngine;

    protected ReversalEngineService $reversalEngine;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'company_id' => 1,
            'branch_id' => 1,
        ]);

        $this->manager = User::factory()->create([
            'company_id' => 1,
            'branch_id' => 1,
        ]);

        $this->ledger = Ledger::create([
            'company_id' => 1,
            'code' => 'LEADING',
            'name' => 'Leading Ledger',
            'is_leading' => true,
        ]);

        $this->cashAccount = ChartOfAccount::create([
            'company_id' => 1,
            'branch_id' => 1,
            'account_code' => '1110',
            'name' => 'Cash in Hand',
            'account_type' => 'asset',
            'normal_balance' => 'debit',
            'is_control_account' => false,
            'is_posting_allowed' => true,
        ]);

        $this->revenueAccount = ChartOfAccount::create([
            'company_id' => 1,
            'branch_id' => 1,
            'account_code' => '4110',
            'name' => 'Sales Revenue',
            'account_type' => 'revenue',
            'normal_balance' => 'credit',
            'is_control_account' => false,
            'is_posting_allowed' => true,
        ]);

        $this->controlAccount = ChartOfAccount::create([
            'company_id' => 1,
            'branch_id' => 1,
            'account_code' => '1200',
            'name' => 'Accounts Receivable Control',
            'account_type' => 'asset',
            'normal_balance' => 'debit',
            'is_control_account' => true,
            'is_posting_allowed' => true,
        ]);

        $this->postingEngine = app(PostingEngineService::class);
        $this->reversalEngine = app(ReversalEngineService::class);
    }

    public function test_journal_must_be_balanced_to_post(): void
    {
        $journal = JournalEntry::create([
            'company_id' => 1,
            'branch_id' => 1,
            'ledger_id' => $this->ledger->id,
            'journal_number' => 'JV-2026-0001',
            'journal_type' => 'MANUAL',
            'transaction_date' => now(),
            'status' => 'DRAFT',
            'created_by' => $this->user->id,
        ]);

        // Unbalanced line amounts
        $journal->lines()->create([
            'account_id' => $this->cashAccount->id,
            'debit_amount' => 1000.00,
            'credit_amount' => 0.00,
            'base_debit_amount' => 1000.00,
            'base_credit_amount' => 0.00,
        ]);

        $journal->lines()->create([
            'account_id' => $this->revenueAccount->id,
            'debit_amount' => 0.00,
            'credit_amount' => 950.00,
            'base_debit_amount' => 0.00,
            'base_credit_amount' => 950.00,
        ]);

        // Assert posting fails with validation exception due to imbalance > 0.05
        $this->expectException(ValidationException::class);
        $this->postingEngine->post($journal, $this->manager->id); // Approved by manager to bypass maker-checker
    }

    public function test_minor_rounding_variance_posts_to_suspense(): void
    {
        $journal = JournalEntry::create([
            'company_id' => 1,
            'branch_id' => 1,
            'ledger_id' => $this->ledger->id,
            'journal_number' => 'JV-2026-0002',
            'journal_type' => 'MANUAL',
            'transaction_date' => now(),
            'status' => 'DRAFT',
            'created_by' => $this->user->id,
        ]);

        // Minor delta = 0.02
        $journal->lines()->create([
            'account_id' => $this->cashAccount->id,
            'debit_amount' => 1000.00,
            'credit_amount' => 0.00,
            'base_debit_amount' => 1000.00,
            'base_credit_amount' => 0.00,
        ]);

        $journal->lines()->create([
            'account_id' => $this->revenueAccount->id,
            'debit_amount' => 0.00,
            'credit_amount' => 999.98,
            'base_debit_amount' => 0.00,
            'base_credit_amount' => 999.98,
        ]);

        $this->postingEngine->post($journal, $this->manager->id);

        $this->assertEquals('POSTED', $journal->status);
        $this->assertDatabaseHas('journal_lines', [
            'journal_entry_id' => $journal->id,
            'credit_amount' => 0.02,
        ]); // Suspense line matches delta
    }

    public function test_manual_posting_to_control_account_is_blocked(): void
    {
        $journal = JournalEntry::create([
            'company_id' => 1,
            'branch_id' => 1,
            'ledger_id' => $this->ledger->id,
            'journal_number' => 'JV-2026-0003',
            'journal_type' => 'MANUAL',
            'transaction_date' => now(),
            'status' => 'DRAFT',
            'created_by' => $this->user->id,
        ]);

        $journal->lines()->create([
            'account_id' => $this->controlAccount->id, // Control Account
            'debit_amount' => 1000.00,
            'credit_amount' => 0.00,
            'base_debit_amount' => 1000.00,
            'base_credit_amount' => 0.00,
        ]);

        $journal->lines()->create([
            'account_id' => $this->revenueAccount->id,
            'debit_amount' => 0.00,
            'credit_amount' => 1000.00,
            'base_debit_amount' => 0.00,
            'base_credit_amount' => 1000.00,
        ]);

        $this->expectException(ValidationException::class);
        $this->postingEngine->post($journal, $this->manager->id);
    }

    public function test_journal_entry_reversal_creates_offset(): void
    {
        $journal = JournalEntry::create([
            'company_id' => 1,
            'branch_id' => 1,
            'ledger_id' => $this->ledger->id,
            'journal_number' => 'JV-2026-0004',
            'journal_type' => 'MANUAL',
            'transaction_date' => now(),
            'status' => 'DRAFT',
            'created_by' => $this->user->id,
        ]);

        $journal->lines()->create([
            'account_id' => $this->cashAccount->id,
            'debit_amount' => 500.00,
            'credit_amount' => 0.00,
            'base_debit_amount' => 500.00,
            'base_credit_amount' => 0.00,
        ]);

        $journal->lines()->create([
            'account_id' => $this->revenueAccount->id,
            'debit_amount' => 0.00,
            'credit_amount' => 500.00,
            'base_debit_amount' => 0.00,
            'base_credit_amount' => 500.00,
        ]);

        $this->postingEngine->post($journal, $this->manager->id);
        $this->assertEquals('POSTED', $journal->status);

        $reversingNum = $this->reversalEngine->reverse($journal->id, $this->manager->id, 'Test correction');

        $this->assertDatabaseHas('journal_entries', [
            'journal_number' => $reversingNum,
            'journal_type' => 'REVERSING',
            'status' => 'POSTED',
        ]);

        $this->assertEquals('REVERSED', $journal->fresh()->status);
    }

    public function test_document_splitting_balances_dimension_segments(): void
    {
        // Setup financial dimension
        $dim = FinancialDimension::create([
            'company_id' => 1,
            'code' => 'DEPARTMENT',
            'name' => 'Department',
            'is_mandatory' => true,
        ]);

        $deptSales = FinancialDimensionValue::create([
            'financial_dimension_id' => $dim->id,
            'code' => 'DEPT_SALES',
            'name' => 'Sales Dept',
        ]);

        $deptIt = FinancialDimensionValue::create([
            'financial_dimension_id' => $dim->id,
            'code' => 'DEPT_IT',
            'name' => 'IT Dept',
        ]);

        $journal = JournalEntry::create([
            'company_id' => 1,
            'branch_id' => 1,
            'ledger_id' => $this->ledger->id,
            'journal_number' => 'JV-2026-0005',
            'journal_type' => 'MANUAL',
            'transaction_date' => now(),
            'status' => 'DRAFT',
            'created_by' => $this->user->id,
        ]);

        // Lines are balanced in total (1000 DR = 1000 CR) but unbalanced by department dimension
        $line1 = $journal->lines()->create([
            'account_id' => $this->cashAccount->id,
            'debit_amount' => 1000.00,
            'credit_amount' => 0.00,
            'base_debit_amount' => 1000.00,
            'base_credit_amount' => 0.00,
        ]);
        $line1->dimensionValues()->attach($deptSales->id, ['financial_dimension_id' => $dim->id]);

        $line2 = $journal->lines()->create([
            'account_id' => $this->revenueAccount->id,
            'debit_amount' => 0.00,
            'credit_amount' => 1000.00,
            'base_debit_amount' => 0.00,
            'base_credit_amount' => 1000.00,
        ]);
        $line2->dimensionValues()->attach($deptIt->id, ['financial_dimension_id' => $dim->id]);

        $this->postingEngine->post($journal, $this->manager->id);

        // Verification: splitting engine created offset lines to balance the segments
        $this->assertCount(4, $journal->fresh()->lines);
    }

    public function test_accounting_gateway_posts_transactions_by_posting_rules(): void
    {
        // Configure posting rule
        PostingRule::create([
            'company_id' => 1,
            'branch_id' => 1,
            'event_type' => 'SALES_INVOICE_APPROVED',
            'debit_account_id' => $this->controlAccount->id,
            'credit_account_id' => $this->revenueAccount->id,
            'description' => 'Invoice posting mapping',
        ]);

        $gateway = app(AccountingGateway::class);

        $jnlNum = $gateway->postTransaction('SALES_INVOICE_APPROVED', [
            'company_id' => 1,
            'branch_id' => 1,
            'ledger_id' => $this->ledger->id,
            'amount' => 750000.00,
            'user_id' => $this->manager->id, // Approved by bypass user
            'transaction_date' => '2026-07-13',
        ]);

        $this->assertDatabaseHas('journal_entries', [
            'journal_number' => $jnlNum,
            'journal_type' => 'AUTOMATIC',
            'status' => 'POSTED',
        ]);
    }

    public function test_posted_journal_digital_signature_is_verifiable(): void
    {
        $journal = JournalEntry::create([
            'company_id' => 1,
            'branch_id' => 1,
            'ledger_id' => $this->ledger->id,
            'journal_number' => 'JV-2026-0006',
            'journal_type' => 'MANUAL',
            'transaction_date' => now(),
            'status' => 'DRAFT',
            'created_by' => $this->user->id,
        ]);

        $journal->lines()->create([
            'account_id' => $this->cashAccount->id,
            'debit_amount' => 100.00,
            'credit_amount' => 0.00,
            'base_debit_amount' => 100.00,
            'base_credit_amount' => 0.00,
        ]);

        $journal->lines()->create([
            'account_id' => $this->revenueAccount->id,
            'debit_amount' => 0.00,
            'credit_amount' => 100.00,
            'base_debit_amount' => 0.00,
            'base_credit_amount' => 100.00,
        ]);

        $this->postingEngine->post($journal, $this->manager->id);

        $signatureService = app(DigitalSignatureService::class);
        $this->assertTrue($signatureService->verify($journal->fresh()));
    }
}
