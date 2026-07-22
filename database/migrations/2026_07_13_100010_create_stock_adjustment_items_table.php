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
        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_adjustment_id')->index()->constrained('stock_adjustments')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->index()->constrained('inventory_items')->restrictOnDelete();

            $table->string('type', 50); // addition, deduction
            $table->decimal('quantity_adjusted', 15, 2);
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
        Schema::dropIfExists('stock_adjustment_items');
    }
};
