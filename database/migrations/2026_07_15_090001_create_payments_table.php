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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 100)->unique()->nullable();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('restrict');
            $table->foreignId('company_id')->nullable();
            $table->foreignId('branch_id')->nullable();
            $table->string('status', 50)->default('draft');
            $table->date('payment_date');
            $table->string('payment_method', 50);
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->decimal('allocated_amount', 15, 2)->default(0.00);
            $table->decimal('unallocated_amount', 15, 2)->default(0.00);

            // Multi-Currency Architecture
            $table->string('base_currency', 10)->default('IDR');
            $table->string('transaction_currency', 10)->default('IDR');
            $table->decimal('exchange_rate', 15, 6)->default(1.000000);
            $table->string('exchange_rate_source', 50)->nullable();
            $table->date('exchange_rate_date')->nullable();
            $table->boolean('exchange_rate_locked')->default(false);
            $table->string('exchange_rate_notes', 255)->nullable();

            $table->string('bank_name', 255)->nullable();
            $table->string('bank_account_no', 100)->nullable();
            $table->string('cheque_no', 100)->nullable();
            $table->string('transaction_ref', 255)->nullable();
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->text('reversal_reason')->nullable();

            // Approvals tracking
            $table->foreignId('submitted_by')->nullable()->constrained('users');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users');
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users');
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'payment_date'], 'idx_payments_status_date');
            $table->index('customer_id', 'idx_payments_customer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
