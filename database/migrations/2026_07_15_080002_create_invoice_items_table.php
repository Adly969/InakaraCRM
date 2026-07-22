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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('sales_order_item_id')->nullable()->constrained('sales_order_items')->nullOnDelete();
            $table->foreignId('delivery_order_item_id')->nullable()->constrained('delivery_order_items')->nullOnDelete();

            $table->string('sku', 100);
            $table->string('description');
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 50)->default('pcs');
            $table->decimal('unit_price', 15, 2);

            $table->decimal('discount_percentage', 5, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('tax_percentage', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2);

            $table->json('item_details_snapshot')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
