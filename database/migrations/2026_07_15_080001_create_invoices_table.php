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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 100)->unique()->nullable();
            $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->nullOnDelete();
            $table->foreignId('delivery_order_id')->nullable()->constrained('delivery_orders')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('company_id')->nullable()->comment('Multi-company readiness stub');
            $table->foreignId('branch_id')->nullable()->comment('Multi-branch readiness stub');
            $table->string('status', 50)->default('draft');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->string('payment_term_code', 50);

            // Financial summary
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('adjustment_amount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('outstanding_balance', 15, 2)->default(0.00);

            $table->string('currency', 10)->default('IDR');
            $table->decimal('exchange_rate', 15, 6)->default(1.000000);

            // Snapshots
            $table->json('billing_address_snapshot');
            $table->json('shipping_address_snapshot');

            $table->text('notes')->nullable();
            $table->text('void_reason')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'invoice_date'], 'idx_invoices_status_date');
            $table->index('reference_no', 'idx_invoices_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
