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
        // 1. Idempotency Key Lock Tracker
        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key_hash', 64)->unique();
            $table->timestamp('expiry_time');
            $table->timestamps();
        });

        // 2. Event Schema Registry Table
        Schema::create('event_schema_registry', function (Blueprint $table) {
            $table->id();
            $table->string('schema_name', 100);
            $table->integer('schema_version');
            $table->json('required_fields');
            $table->json('optional_fields');
            $table->json('deprecated_fields')->nullable();
            $table->string('payload_hash', 64);
            $table->json('validation_rules');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['schema_name', 'schema_version'], 'uq_schema_version');
        });

        // 3. Ingested Financial Events Log
        Schema::create('financial_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_uuid', 36)->unique();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('event_type', 100);
            $table->string('source_module', 50);
            $table->json('payload');
            $table->string('status', 30)->default('RECEIVED'); // RECEIVED, VALIDATED, QUEUED, POSTED, FAILED
            $table->string('idempotency_key', 64);
            $table->string('correlation_id', 36);
            $table->timestamps();

            $table->unique(['company_id', 'idempotency_key'], 'uq_idempotency');
            $table->index(['company_id', 'status'], 'idx_event_status');
        });

        // 4. Posting Rule Versioning Configuration Table
        Schema::create('posting_rule_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('event_type', 100);
            $table->integer('version')->default(1);
            $table->integer('priority')->default(0);
            $table->string('status', 30)->default('DRAFT'); // DRAFT, PUBLISHED, ARCHIVED
            $table->unsignedBigInteger('debit_account_id');
            $table->unsignedBigInteger('credit_account_id');
            $table->timestamp('effective_from');
            $table->timestamp('effective_until')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('superseded_by')->nullable();
            $table->timestamps();

            $table->foreign('debit_account_id')->references('id')->on('chart_of_accounts');
            $table->foreign('credit_account_id')->references('id')->on('chart_of_accounts');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->foreign('published_by')->references('id')->on('users');
        });

        // 5. Posting Rule Audits Trail Table (Immutable Logs)
        Schema::create('posting_rule_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rule_id');
            $table->string('action', 30); // CREATE, UPDATE, PUBLISH, ARCHIVE
            $table->json('before_state')->nullable();
            $table->json('after_state')->nullable();
            $table->unsignedBigInteger('changed_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('correlation_id', 36);
            $table->timestamps();

            $table->foreign('changed_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');
        });

        // 6. Queue Posting Jobs Tracking
        Schema::create('posting_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->string('status', 30)->default('PENDING'); // PENDING, PROCESSING, SUCCESS, FAILED
            $table->integer('retry_count')->default(0);
            $table->integer('max_retries')->default(3);
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('financial_events');
        });

        // 7. Dead Letter Queue / Posting Failures
        Schema::create('posting_failures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_id');
            $table->string('event_type', 100);
            $table->json('failed_payload');
            $table->string('failure_reason', 255);
            $table->text('stack_trace')->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('job_id')->references('id')->on('posting_jobs');
            $table->foreign('resolved_by')->references('id')->on('users');
        });

        // 8. Transactional Event Outbox Table
        Schema::create('event_outbox', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('event_type', 100);
            $table->json('payload');
            $table->string('idempotency_key', 64);
            $table->boolean('is_dispatched')->default(false);
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamps();

            $table->index(['is_dispatched', 'created_at'], 'idx_outbox_dispatch');
        });

        // 9. Posting Simulation Log Table
        Schema::create('simulation_results', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 100);
            $table->json('payload');
            $table->json('simulated_journal');
            $table->unsignedBigInteger('run_by');
            $table->timestamps();

            $table->foreign('run_by')->references('id')->on('users');
        });

        // 10. Saga Transaction Logs & Distributed Steps Tracker
        Schema::create('saga_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('saga_type', 100);
            $table->string('status', 30)->default('STARTED'); // STARTED, COMPLETED, FAILED, COMPENSATING, COMPENSATED
            $table->string('correlation_id', 36);
            $table->timestamps();
        });

        Schema::create('saga_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('saga_transaction_id');
            $table->string('step_name', 100);
            $table->string('status', 30); // PENDING, COMPLETED, FAILED
            $table->json('payload')->nullable();
            $table->json('compensation_payload')->nullable();
            $table->timestamps();

            $table->foreign('saga_transaction_id')->references('id')->on('saga_transactions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saga_steps');
        Schema::dropIfExists('saga_transactions');
        Schema::dropIfExists('simulation_results');
        Schema::dropIfExists('event_outbox');
        Schema::dropIfExists('posting_failures');
        Schema::dropIfExists('posting_jobs');
        Schema::dropIfExists('posting_rule_audits');
        Schema::dropIfExists('posting_rule_versions');
        Schema::dropIfExists('financial_events');
        Schema::dropIfExists('event_schema_registry');
        Schema::dropIfExists('idempotency_keys');
    }
};
