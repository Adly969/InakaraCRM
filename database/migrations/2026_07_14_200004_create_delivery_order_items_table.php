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
        Schema::create('delivery_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_order_id')->constrained('delivery_orders')->onDelete('cascade');
            $table->foreignId('sales_order_item_id')->constrained('sales_order_items');
            $table->string('sku', 100);
            $table->string('description', 255);
            $table->decimal('quantity_requested', 15, 2);
            $table->decimal('quantity_shipped', 15, 2)->default(0.00);
            $table->decimal('quantity_delivered', 15, 2)->default(0.00);
            $table->string('unit', 50)->default('pcs');
            $table->json('item_specifications_snapshot');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_order_items');
    }
};
