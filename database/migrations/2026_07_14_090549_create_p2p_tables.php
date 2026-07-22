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
        // 1. Vendors Master Profile
        Schema::create('p2p_vendors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('code', 50);
            $table->string('name', 150);
            $table->string('category', 50);
            $table->string('qualification_status', 30)->default('PROBATION');
            $table->string('tax_number', 50)->nullable();
            $table->string('currency_code', 10)->default('IDR');
            $table->string('payment_terms_code', 50);
            $table->string('risk_level', 20)->default('MEDIUM');
            $table->decimal('esg_score', 5, 2)->default(100.00);
            $table->integer('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code']);
        });

        // 2. Vendor Certifications Tracking
        Schema::create('p2p_vendor_certifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('name', 100);
            $table->string('certificate_no', 100);
            $table->string('issuing_authority', 150);
            $table->date('valid_from');
            $table->date('valid_to');
            $table->string('status', 30)->default('ACTIVE');
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('p2p_vendors')->onDelete('cascade');
        });

        // 3. Vendor Banking Profiles
        Schema::create('p2p_vendor_banking', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('bank_name', 100);
            $table->text('account_number'); // Encrypted account number
            $table->string('account_name', 150);
            $table->string('swift_code', 30)->nullable();
            $table->string('routing_code', 50)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('p2p_vendors')->onDelete('cascade');
        });

        // 4. Supplier Contracts Lifecycle
        Schema::create('p2p_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('contract_no', 100);
            $table->string('type', 30);
            $table->string('status', 30)->default('DRAFT');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_value_limit', 15, 2);
            $table->decimal('released_value', 15, 2)->default(0.00);
            $table->integer('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vendor_id')->references('id')->on('p2p_vendors')->onDelete('set null');
            $table->unique(['company_id', 'contract_no']);
        });

        // 5. Budgets Control Matrix
        Schema::create('p2p_budgets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('cost_center_code', 50);
            $table->integer('fiscal_year');
            $table->decimal('allocated_amount', 15, 2);
            $table->decimal('reserved_amount', 15, 2)->default(0.00);
            $table->decimal('committed_amount', 15, 2)->default(0.00);
            $table->decimal('actual_spent_amount', 15, 2)->default(0.00);
            $table->string('status', 30)->default('ACTIVE');
            $table->integer('version')->default(1);
            $table->timestamps();

            $table->unique(['company_id', 'cost_center_code', 'fiscal_year']);
        });

        // 6. Purchase Requisitions Table
        Schema::create('p2p_requisitions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('requisition_no', 100);
            $table->unsignedBigInteger('requester_id');
            $table->string('cost_center_code', 50);
            $table->string('type', 30);
            $table->string('status', 30)->default('DRAFT');
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->integer('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'requisition_no']);
        });

        // 7. Purchase Requisition Lines
        Schema::create('p2p_requisition_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requisition_id');
            $table->string('sku', 50);
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price_estimate', 15, 2);
            $table->timestamps();

            $table->foreign('requisition_id')->references('id')->on('p2p_requisitions')->onDelete('cascade');
        });

        // 8. RFQ Sourcing Matrix
        Schema::create('p2p_rfqs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('rfq_no', 100);
            $table->string('status', 30)->default('OPEN');
            $table->timestamp('close_date');
            $table->integer('version')->default(1);
            $table->timestamps();

            $table->unique(['company_id', 'rfq_no']);
        });

        // 9. RFQ Bids Mappings
        Schema::create('p2p_bids', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rfq_id');
            $table->unsignedBigInteger('vendor_id');
            $table->string('bid_no', 100);
            $table->decimal('technical_score', 5, 2)->default(0.00);
            $table->decimal('commercial_quote', 15, 2)->default(0.00);
            $table->boolean('is_awarded')->default(false);
            $table->timestamps();

            $table->foreign('rfq_id')->references('id')->on('p2p_rfqs')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('p2p_vendors')->onDelete('cascade');
        });

        // 10. Purchase Orders Table
        Schema::create('p2p_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->string('po_no', 100);
            $table->string('type', 30)->default('STANDARD');
            $table->string('status', 30)->default('DRAFT');
            $table->string('currency_code', 10)->default('IDR');
            $table->decimal('exchange_rate', 12, 4)->default(1.0000);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->integer('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vendor_id')->references('id')->on('p2p_vendors')->onDelete('set null');
            $table->foreign('contract_id')->references('id')->on('p2p_contracts')->onDelete('set null');
            $table->unique(['company_id', 'po_no']);
        });

        // 11. Purchase Order Line Items
        Schema::create('p2p_purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');
            $table->string('sku', 50);
            $table->decimal('quantity_ordered', 12, 2);
            $table->decimal('quantity_received', 12, 2)->default(0.00);
            $table->decimal('quantity_invoiced', 12, 2)->default(0.00);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('tax_percentage', 5, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('purchase_order_id')->references('id')->on('p2p_purchase_orders')->onDelete('cascade');
        });

        // 12. Goods Receipts Table
        Schema::create('p2p_goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->string('receipt_no', 100);
            $table->timestamp('received_at');
            $table->integer('version')->default(1);
            $table->timestamps();

            $table->foreign('purchase_order_id')->references('id')->on('p2p_purchase_orders')->onDelete('set null');
            $table->unique(['company_id', 'receipt_no']);
        });

        // 13. Goods Receipt Items Detail
        Schema::create('p2p_goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('goods_receipt_id');
            $table->unsignedBigInteger('purchase_order_item_id')->nullable();
            $table->string('sku', 50);
            $table->decimal('quantity_received', 12, 2);
            $table->decimal('quantity_accepted', 12, 2);
            $table->decimal('quantity_rejected', 12, 2)->default(0.00);
            $table->string('status', 30)->default('PENDING_QC');
            $table->timestamps();

            $table->foreign('goods_receipt_id')->references('id')->on('p2p_goods_receipts')->onDelete('cascade');
            $table->foreign('purchase_order_item_id')->references('id')->on('p2p_purchase_order_items')->onDelete('set null');
        });

        // 14. Vendor Invoices (5-Way Match Table)
        Schema::create('p2p_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('invoice_no', 100);
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->decimal('amount_invoiced', 15, 2);
            $table->string('matching_status', 30)->default('PENDING');
            $table->string('hold_reason_code', 50)->nullable();
            $table->integer('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vendor_id')->references('id')->on('p2p_vendors')->onDelete('set null');
            $table->foreign('purchase_order_id')->references('id')->on('p2p_purchase_orders')->onDelete('set null');
            $table->unique(['company_id', 'invoice_no']);
        });

        // 15. Invoices Line Items
        Schema::create('p2p_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('purchase_order_item_id')->nullable();
            $table->decimal('quantity_invoiced', 12, 2);
            $table->decimal('unit_price_invoiced', 15, 2);
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('p2p_invoices')->onDelete('cascade');
            $table->foreign('purchase_order_item_id')->references('id')->on('p2p_purchase_order_items')->onDelete('set null');
        });

        // 16. Payment Proposals
        Schema::create('p2p_payment_proposals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('proposal_no', 100);
            $table->string('status', 30)->default('DRAFT');
            $table->decimal('total_payout_amount', 15, 2);
            $table->integer('version')->default(1);
            $table->timestamps();

            $table->unique(['company_id', 'proposal_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p2p_payment_proposals');
        Schema::dropIfExists('p2p_invoice_items');
        Schema::dropIfExists('p2p_invoices');
        Schema::dropIfExists('p2p_goods_receipt_items');
        Schema::dropIfExists('p2p_goods_receipts');
        Schema::dropIfExists('p2p_purchase_order_items');
        Schema::dropIfExists('p2p_purchase_orders');
        Schema::dropIfExists('p2p_bids');
        Schema::dropIfExists('p2p_rfqs');
        Schema::dropIfExists('p2p_requisition_items');
        Schema::dropIfExists('p2p_requisitions');
        Schema::dropIfExists('p2p_budgets');
        Schema::dropIfExists('p2p_contracts');
        Schema::dropIfExists('p2p_vendor_banking');
        Schema::dropIfExists('p2p_vendor_certifications');
        Schema::dropIfExists('p2p_vendors');
    }
};
