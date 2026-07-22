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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->nullable()->unique();
            $table->foreignId('quotation_id')->nullable()->index()->constrained('quotations')->nullOnDelete();
            $table->foreignId('customer_id')->index()->constrained('customers')->restrictOnDelete();
            $table->string('subject');
            $table->string('status')->default('draft')->index();
            $table->text('delivery_terms')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            $table->string('currency', 3)->default('IDR');
            $table->decimal('tax_rate', 5, 2)->default(11.00);
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);

            $table->foreignId('assigned_to')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
