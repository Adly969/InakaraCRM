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
        // 1. Customer Contracts Table
        Schema::create('customer_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('customer_id');
            $table->string('contract_number', 50);
            $table->string('status', 30)->default('DRAFT'); // DRAFT, ACTIVE, EXPIRED, TERMINATED
            $table->date('start_date');
            $table->date('end_date');
            $table->text('terms_conditions')->nullable();
            $table->decimal('credit_limit_override', 15, 2)->nullable();
            $table->integer('version')->default(1);
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers');
            $table->unique(['company_id', 'contract_number'], 'uq_contract_no');
        });

        // 2. Price Books Table
        Schema::create('price_books', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('price_book_name', 100);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->string('currency', 3)->default('IDR');
            $table->timestamps();
        });

        // 3. Price Book Entries Table
        Schema::create('price_book_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('price_book_id');
            $table->string('sku', 100);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('min_quantity', 10, 2)->default(1.00);
            $table->timestamps();

            $table->foreign('price_book_id')->references('id')->on('price_books')->onDelete('cascade');
            $table->unique(['price_book_id', 'sku', 'min_quantity'], 'uq_price_sku');
        });

        // 4. Sales Agreements Table
        Schema::create('sales_agreements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('customer_id');
            $table->string('agreement_number', 50);
            $table->decimal('commitment_amount', 15, 2);
            $table->decimal('consumed_amount', 15, 2)->default(0.00);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('version')->default(1);
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers');
            $table->unique(['company_id', 'agreement_number'], 'uq_agreement_no');
        });

        // 5. Sales Order Revisions Table
        Schema::create('sales_orders_revisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_order_id');
            $table->integer('revision_number');
            $table->json('change_log');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('sales_order_id')->references('id')->on('sales_orders')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users');
        });

        // 6. Sales Order Promotions & Voucher Usage Table
        Schema::create('sales_order_promotions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('sales_order_id');
            $table->string('promotion_code', 50);
            $table->decimal('discount_amount', 15, 2);
            $table->timestamps();

            $table->foreign('sales_order_id')->references('id')->on('sales_orders')->onDelete('cascade');
        });

        // 7. Sales Commission Management Table
        Schema::create('sales_commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('sales_order_id');
            $table->unsignedBigInteger('salesperson_id');
            $table->decimal('commission_rate', 5, 4); // percentage rate (e.g. 0.0500 for 5%)
            $table->decimal('commission_amount', 15, 2);
            $table->string('status', 30)->default('PENDING'); // PENDING, APPROVED, PAID
            $table->timestamps();

            $table->foreign('sales_order_id')->references('id')->on('sales_orders')->onDelete('cascade');
            $table->foreign('salesperson_id')->references('id')->on('users');
        });

        // 8. Outbox Table
        Schema::create('sales_event_outbox', function (Blueprint $table) {
            $table->id();
            $table->string('event_id', 36);
            $table->unsignedBigInteger('company_id');
            $table->string('event_type', 100);
            $table->json('payload');
            $table->string('correlation_id', 36);
            $table->string('causation_id', 36);
            $table->string('trace_id', 36);
            $table->string('idempotency_key', 64);
            $table->boolean('is_dispatched')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_event_outbox');
        Schema::dropIfExists('sales_commissions');
        Schema::dropIfExists('sales_order_promotions');
        Schema::dropIfExists('sales_orders_revisions');
        Schema::dropIfExists('sales_agreements');
        Schema::dropIfExists('price_book_entries');
        Schema::dropIfExists('price_books');
        Schema::dropIfExists('customer_contracts');
    }
};
