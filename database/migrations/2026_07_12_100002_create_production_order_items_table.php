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
        Schema::create('production_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->foreignId('sales_order_item_id')->nullable()->index()->constrained('sales_order_items')->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity', 15, 2);
            $table->string('unit')->default('pcs');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_order_items');
    }
};
