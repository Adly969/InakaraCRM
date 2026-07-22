<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Chart of Accounts
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('account_code', 30);
            $table->string('name', 150);
            $table->string('account_type', 50); // asset, liability, equity, revenue, expense, cogs
            $table->string('normal_balance', 10); // debit, credit
            $table->boolean('is_control_account')->default(false);
            $table->boolean('is_posting_allowed')->default(true);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
            $table->unique(['company_id', 'account_code'], 'uq_coa_code');
        });

        // 2. Journal Entries
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('ledger_id');
            $table->string('journal_number', 50);
            $table->string('status', 30)->default('DRAFT'); // DRAFT, PENDING_APPROVAL, APPROVED, POSTED, REVERSED
            $table->string('journal_type', 30); // MANUAL, AUTOMATIC, REVERSING, ACCRUAL, CLOSING
            $table->date('transaction_date');
            $table->date('reverse_on_date')->nullable();
            $table->string('posting_hash', 64)->nullable(); // HMAC-SHA256 signature
            $table->integer('current_version')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('posted_by')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->foreign('ledger_id')->references('id')->on('ledgers');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->foreign('posted_by')->references('id')->on('users');
            $table->unique(['company_id', 'journal_number'], 'uq_journal_no');
        });

        // 3. Journal Revisions
        Schema::create('journal_revisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journal_entry_id');
            $table->integer('version_number');
            $table->json('changes');
            $table->string('reason', 255);
            $table->unsignedBigInteger('editor_id');
            $table->timestamps();

            $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->onDelete('cascade');
            $table->foreign('editor_id')->references('id')->on('users');
        });

        // 4. Journal Lines
        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journal_entry_id');
            $table->unsignedBigInteger('account_id');
            $table->decimal('debit_amount', 15, 2)->default(0.00);
            $table->decimal('credit_amount', 15, 2)->default(0.00);
            $table->string('currency_code', 3)->default('IDR');
            $table->decimal('exchange_rate', 15, 6)->default(1.000000);
            $table->decimal('base_debit_amount', 15, 2)->default(0.00);
            $table->decimal('base_credit_amount', 15, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('chart_of_accounts');
        });

        // 5. Journal Line Dimensions mapping
        Schema::create('journal_line_dimensions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journal_line_id');
            $table->unsignedBigInteger('financial_dimension_id');
            $table->unsignedBigInteger('financial_dimension_value_id');
            $table->timestamps();

            $table->foreign('journal_line_id')->references('id')->on('journal_lines')->onDelete('cascade');
            $table->foreign('financial_dimension_id')->references('id')->on('financial_dimensions');
            $table->foreign('financial_dimension_value_id', 'fk_jld_val_id')->references('id')->on('financial_dimension_values');
            $table->unique(['journal_line_id', 'financial_dimension_id'], 'uq_jld');
        });

        // 6. Ledger Snapshots (SAP-Style)
        Schema::create('ledger_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('ledger_id');
            $table->unsignedBigInteger('account_id');
            $table->integer('fiscal_year');
            $table->integer('fiscal_month');
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->decimal('total_debits', 15, 2)->default(0.00);
            $table->decimal('total_credits', 15, 2)->default(0.00);
            $table->decimal('closing_balance', 15, 2)->default(0.00);
            $table->boolean('is_frozen')->default(false);
            $table->timestamps();

            $table->foreign('ledger_id')->references('id')->on('ledgers');
            $table->foreign('account_id')->references('id')->on('chart_of_accounts');
            $table->unique(['company_id', 'branch_id', 'ledger_id', 'account_id', 'fiscal_year', 'fiscal_month'], 'uq_ledger_snap');
        });

        // 7. Intercompany Mappings
        Schema::create('intercompany_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_company_id');
            $table->unsignedBigInteger('destination_company_id');
            $table->unsignedBigInteger('due_from_account_id');
            $table->unsignedBigInteger('due_to_account_id');
            $table->timestamps();

            $table->foreign('due_from_account_id')->references('id')->on('chart_of_accounts');
            $table->foreign('due_to_account_id')->references('id')->on('chart_of_accounts');
        });

        // 8. Budget Entries & Dimensions
        Schema::create('budget_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('account_id');
            $table->integer('fiscal_year');
            $table->decimal('budget_amount', 15, 2)->default(0.00);
            $table->integer('revision_number')->default(1);
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('chart_of_accounts');
        });

        Schema::create('budget_dimensions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_entry_id');
            $table->unsignedBigInteger('financial_dimension_id');
            $table->unsignedBigInteger('financial_dimension_value_id');
            $table->timestamps();

            $table->foreign('budget_entry_id')->references('id')->on('budget_entries')->onDelete('cascade');
            $table->foreign('financial_dimension_id')->references('id')->on('financial_dimensions');
            $table->foreign('financial_dimension_value_id', 'fk_bd_val_id')->references('id')->on('financial_dimension_values');
        });

        // 8.5 Journal Reversals
        Schema::create('journal_reversals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_journal_id');
            $table->unsignedBigInteger('reversing_journal_id');
            $table->unsignedBigInteger('reversed_by');
            $table->string('reason', 255);
            $table->timestamps();

            $table->foreign('original_journal_id')->references('id')->on('journal_entries');
            $table->foreign('reversing_journal_id')->references('id')->on('journal_entries');
            $table->foreign('reversed_by')->references('id')->on('users');
        });

        // 9. Recurring Journals
        Schema::create('recurring_journals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('name', 100);
            $table->string('frequency', 20); // DAILY, WEEKLY, MONTHLY, QUARTERLY, YEARLY
            $table->json('template_lines');
            $table->date('next_execution_date');
            $table->date('expiration_date')->nullable();
            $table->boolean('skip_holidays')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 10. Configurable Posting Rules Mapping
        Schema::create('posting_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('event_type', 100);
            $table->string('transaction_attribute', 100)->nullable();
            $table->unsignedBigInteger('debit_account_id');
            $table->unsignedBigInteger('credit_account_id');
            $table->string('description', 255)->nullable();
            $table->timestamps();

            $table->foreign('debit_account_id')->references('id')->on('chart_of_accounts');
            $table->foreign('credit_account_id')->references('id')->on('chart_of_accounts');
            $table->unique(['company_id', 'branch_id', 'event_type', 'transaction_attribute'], 'uq_posting_rule_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_reversals');
        Schema::dropIfExists('posting_rules');
        Schema::dropIfExists('recurring_journals');
        Schema::dropIfExists('budget_dimensions');
        Schema::dropIfExists('budget_entries');
        Schema::dropIfExists('intercompany_mappings');
        Schema::dropIfExists('ledger_snapshots');
        Schema::dropIfExists('journal_line_dimensions');
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journal_revisions');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('chart_of_accounts');
    }
};
