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
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 100)->unique();
            $table->foreignId('sales_order_id')->constrained('sales_orders');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('company_id')->nullable()->comment('Multi-company readiness stub');
            $table->foreignId('branch_id')->nullable()->comment('Multi-branch readiness stub');
            $table->string('status', 50)->default('draft');
            $table->json('shipping_address_snapshot');
            $table->json('billing_address_snapshot');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['sales_order_id', 'company_id'], 'idx_do_so_company');
            $table->index('status', 'idx_do_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
    }
};
