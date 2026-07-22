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
        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->index()->constrained('goods_receipts')->cascadeOnDelete();
            $table->foreignId('production_order_item_id')->nullable()->index()->constrained('production_order_items')->nullOnDelete();
            $table->string('sku', 100)->index();
            $table->string('description');

            $table->decimal('quantity_received', 15, 2);
            $table->string('unit', 50)->default('pcs');
            $table->decimal('unit_cost', 15, 2)->default(0.00);

            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_items');
    }
};
