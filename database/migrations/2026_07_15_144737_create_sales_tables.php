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
        // 1. Pipeline Stages Table
        if (! Schema::hasTable('pipeline_stages')) {
            Schema::create('pipeline_stages', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->string('name', 100);
                $table->decimal('probability', 5, 2);
                $table->integer('sort_order');
                $table->boolean('is_system')->default(false);
                $table->integer('version')->default(1);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['tenant_id', 'name']);
            });
        }

        // 2. Opportunities Table
        if (! Schema::hasTable('opportunities')) {
            Schema::create('opportunities', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->string('title', 200);
                $table->unsignedBigInteger('customer_id')->index();
                $table->uuid('stage_id')->index();
                $table->decimal('amount', 15, 4);
                $table->decimal('probability', 5, 2);
                $table->decimal('expected_revenue', 15, 4);
                $table->date('expected_close_date');
                $table->string('priority', 50);
                $table->string('source', 100)->nullable();
                $table->text('lost_reason')->nullable();
                $table->string('competitor', 100)->nullable();
                $table->string('forecast_category', 50);
                $table->unsignedBigInteger('assigned_to')->index();
                $table->string('status', 50)->default('open');
                $table->integer('version')->default(1);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('stage_id')->references('id')->on('pipeline_stages')->cascadeOnDelete();
                $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            });
        }

        // Add opportunity_id to quotations if not exists
        if (Schema::hasTable('quotations') && ! Schema::hasColumn('quotations', 'opportunity_id')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->uuid('opportunity_id')->nullable()->index()->after('customer_id');
                $table->foreign('opportunity_id')->references('id')->on('opportunities')->nullOnDelete();
            });
        }

        // 3. Credit Limits Table
        if (! Schema::hasTable('credit_limits')) {
            Schema::create('credit_limits', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->unsignedBigInteger('customer_id')->unique();
                $table->decimal('credit_limit', 15, 4)->default(0.00);
                $table->decimal('outstanding_receivables', 15, 4)->default(0.00);
                $table->decimal('pending_invoices', 15, 4)->default(0.00);
                $table->decimal('pending_sales_orders', 15, 4)->default(0.00);
                $table->boolean('is_on_hold')->default(false);
                $table->string('risk_category', 50)->default('medium');
                $table->integer('credit_score')->nullable();
                $table->integer('version')->default(1);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            });
        }

        // 4. Credit History Table
        if (! Schema::hasTable('credit_history')) {
            Schema::create('credit_history', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->unsignedBigInteger('customer_id')->index();
                $table->decimal('previous_limit', 15, 4);
                $table->decimal('new_limit', 15, 4);
                $table->unsignedBigInteger('adjusted_by');
                $table->text('reason')->nullable();
                $table->timestamps();

                $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            });
        }

        // 5. Credit Reviews Table
        if (! Schema::hasTable('credit_reviews')) {
            Schema::create('credit_reviews', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->unsignedBigInteger('customer_id')->index();
                $table->unsignedBigInteger('sales_order_id')->nullable()->index();
                $table->decimal('requested_amount', 15, 4);
                $table->string('decision', 50);
                $table->unsignedBigInteger('decision_by')->nullable();
                $table->text('justification')->nullable();
                $table->timestamps();

                $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
                $table->foreign('sales_order_id')->references('id')->on('sales_orders')->nullOnDelete();
            });
        }

        // 6. Allocation Logs Table (referencing existing payment_allocations by unsignedBigInteger)
        if (! Schema::hasTable('allocation_logs')) {
            Schema::create('allocation_logs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->unsignedBigInteger('payment_allocation_id')->index();
                $table->string('action', 100);
                $table->unsignedBigInteger('actor_id');
                $table->text('reason')->nullable();
                $table->timestamps();

                $table->foreign('payment_allocation_id')->references('id')->on('payment_allocations')->cascadeOnDelete();
            });
        }

        // 7. Document Snapshots Table
        if (! Schema::hasTable('document_snapshots')) {
            Schema::create('document_snapshots', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('document_type', 100);
                $table->unsignedBigInteger('document_id')->index();
                $table->string('customer_name', 255);
                $table->jsonb('billing_address');
                $table->jsonb('shipping_address');
                $table->string('tax_id', 100)->nullable();
                $table->string('currency', 3)->default('IDR');
                $table->string('payment_terms', 100)->nullable();
                $table->string('agent_name', 255)->nullable();
                $table->timestamps();
            });
        }

        // 8. Currency Rates Table
        if (! Schema::hasTable('currency_rates')) {
            Schema::create('currency_rates', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('currency_code', 3);
                $table->decimal('rate', 12, 6);
                $table->date('effective_date');
                $table->string('rate_provider', 100);
                $table->timestamps();

                $table->unique(['tenant_id', 'currency_code', 'effective_date']);
            });
        }

        // 9. Exchange Rate History Table
        if (! Schema::hasTable('exchange_rate_history')) {
            Schema::create('exchange_rate_history', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('currency_code', 3);
                $table->decimal('previous_rate', 12, 6);
                $table->decimal('new_rate', 12, 6);
                $table->timestamps();
            });
        }

        // 10. Credit Notes Table (referencing existing invoices by unsignedBigInteger)
        if (! Schema::hasTable('credit_notes')) {
            Schema::create('credit_notes', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->unsignedBigInteger('invoice_id')->index();
                $table->string('credit_note_no', 100);
                $table->decimal('amount', 15, 4);
                $table->string('type', 50);
                $table->string('status', 50)->default('draft');
                $table->integer('version')->default(1);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
            });
        }

        // 11. Debit Notes Table (referencing existing invoices by unsignedBigInteger)
        if (! Schema::hasTable('debit_notes')) {
            Schema::create('debit_notes', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->unsignedBigInteger('invoice_id')->index();
                $table->string('debit_note_no', 100);
                $table->decimal('amount', 15, 4);
                $table->string('type', 50);
                $table->string('status', 50)->default('draft');
                $table->integer('version')->default(1);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
            });
        }

        // 12. Document Attachments Table
        if (! Schema::hasTable('document_attachments')) {
            Schema::create('document_attachments', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('attachable_type', 100);
                $table->unsignedBigInteger('attachable_id')->index();
                $table->string('disk', 50);
                $table->string('path', 255);
                $table->string('filename', 255);
                $table->string('mime_type', 100);
                $table->bigInteger('size');
                $table->integer('version')->default(1);
                $table->unsignedBigInteger('uploaded_by')->nullable();
                $table->timestamps();
            });
        }

        // 13. Comments Table
        if (! Schema::hasTable('comments')) {
            Schema::create('comments', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('commentable_type', 100);
                $table->unsignedBigInteger('commentable_id')->index();
                $table->unsignedBigInteger('user_id');
                $table->text('body');
                $table->uuid('parent_id')->nullable()->index();
                $table->boolean('is_pinned')->default(false);
                $table->timestamps();
            });
        }

        // 14. Business Calendars Table
        if (! Schema::hasTable('business_calendars')) {
            Schema::create('business_calendars', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->integer('fiscal_year');
                $table->date('period_start');
                $table->date('period_end');
                $table->jsonb('holidays');
                $table->string('status', 50)->default('open');
                $table->timestamps();
            });
        }

        // 15. Workflow Definitions Table
        if (! Schema::hasTable('workflow_definitions')) {
            Schema::create('workflow_definitions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('name', 200);
                $table->string('trigger_event', 200);
                $table->string('status', 50)->default('active');
                $table->timestamps();
            });
        }

        // 16. Workflow Steps Table
        if (! Schema::hasTable('workflow_steps')) {
            Schema::create('workflow_steps', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('workflow_definition_id')->index();
                $table->integer('step_number');
                $table->string('approver_role', 100);
                $table->integer('timeout_hours')->nullable();
                $table->timestamps();

                $table->foreign('workflow_definition_id')->references('id')->on('workflow_definitions')->cascadeOnDelete();
            });
        }

        // 17. Workflow Conditions Table
        if (! Schema::hasTable('workflow_conditions')) {
            Schema::create('workflow_conditions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('workflow_step_id')->index();
                $table->string('field', 100);
                $table->string('operator', 50);
                $table->string('value', 255);
                $table->timestamps();

                $table->foreign('workflow_step_id')->references('id')->on('workflow_steps')->cascadeOnDelete();
            });
        }

        // 18. Workflow Logs Table
        if (! Schema::hasTable('workflow_logs')) {
            Schema::create('workflow_logs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('workflow_step_id')->index();
                $table->unsignedBigInteger('document_id')->index();
                $table->string('action', 50);
                $table->unsignedBigInteger('actor_id');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('workflow_step_id')->references('id')->on('workflow_steps')->cascadeOnDelete();
            });
        }

        // 19. Business Rules Table
        if (! Schema::hasTable('business_rules')) {
            Schema::create('business_rules', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('rule_type', 100);
                $table->jsonb('conditions');
                $table->jsonb('actions');
                $table->date('effective_date');
                $table->date('expiration_date');
                $table->integer('priority')->default(1);
                $table->timestamps();
            });
        }

        // 20. Document Locks Table
        if (! Schema::hasTable('document_locks')) {
            Schema::create('document_locks', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('lockable_type', 100);
                $table->unsignedBigInteger('lockable_id')->index();
                $table->unsignedBigInteger('locked_by');
                $table->timestamp('locked_at');
                $table->timestamp('expires_at');
                $table->string('reason', 255);
                $table->timestamps();
            });
        }

        // 21. Tenant Settings Table
        if (! Schema::hasTable('tenant_settings')) {
            Schema::create('tenant_settings', function (Blueprint $table) {
                $table->uuid('tenant_id')->primary();
                $table->string('default_currency', 3)->default('IDR');
                $table->integer('decimal_precision')->default(2);
                $table->string('invoice_prefix', 50)->default('INV');
                $table->string('so_prefix', 50)->default('SO');
                $table->string('credit_limit_policy', 50)->default('block');
                $table->integer('reminder_days')->default(7);
                $table->timestamps();
            });
        }

        // 22. AI Events Table
        if (! Schema::hasTable('ai_events')) {
            Schema::create('ai_events', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('entity_type', 100);
                $table->unsignedBigInteger('entity_id')->index();
                $table->decimal('metric_score', 5, 2);
                $table->jsonb('risk_factors');
                $table->text('summary')->nullable();
                $table->timestamps();
            });
        }

        // 23. Sales Dashboard Projections
        if (! Schema::hasTable('sales_dashboard_projections')) {
            Schema::create('sales_dashboard_projections', function (Blueprint $table) {
                $table->uuid('tenant_id');
                $table->string('metric_key', 100);
                $table->decimal('metric_value', 20, 4);
                $table->timestamp('last_updated_at');

                $table->primary(['tenant_id', 'metric_key']);
            });
        }

        // 24. Sales Event Outbox
        if (! Schema::hasTable('sales_event_outbox')) {
            Schema::create('sales_event_outbox', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->uuid('tenant_id')->index();
                $table->string('event_type', 255);
                $table->jsonb('payload');
                $table->timestamp('processed_at')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_event_outbox');
        Schema::dropIfExists('sales_dashboard_projections');
        Schema::dropIfExists('ai_events');
        Schema::dropIfExists('tenant_settings');
        Schema::dropIfExists('document_locks');
        Schema::dropIfExists('business_rules');
        Schema::dropIfExists('workflow_logs');
        Schema::dropIfExists('workflow_conditions');
        Schema::dropIfExists('workflow_steps');
        Schema::dropIfExists('workflow_definitions');
        Schema::dropIfExists('business_calendars');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('document_attachments');
        Schema::dropIfExists('debit_notes');
        Schema::dropIfExists('credit_notes');
        Schema::dropIfExists('exchange_rate_history');
        Schema::dropIfExists('currency_rates');
        Schema::dropIfExists('document_snapshots');
        Schema::dropIfExists('allocation_logs');
        Schema::dropIfExists('credit_reviews');
        Schema::dropIfExists('credit_history');
        Schema::dropIfExists('credit_limits');
        Schema::dropIfExists('opportunities');
        Schema::dropIfExists('pipeline_stages');

        if (Schema::hasTable('quotations') && Schema::hasColumn('quotations', 'opportunity_id')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->dropForeign(['opportunity_id']);
                $table->dropColumn('opportunity_id');
            });
        }
    }
};
