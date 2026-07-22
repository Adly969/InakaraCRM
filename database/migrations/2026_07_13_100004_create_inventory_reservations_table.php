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
        Schema::create('inventory_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->index()->constrained('sales_orders')->restrictOnDelete();
            $table->foreignId('inventory_item_id')->index()->constrained('inventory_items')->restrictOnDelete();
            $table->decimal('quantity_reserved', 15, 2);
            $table->decimal('quantity_released', 15, 2)->default(0.00);
            $table->string('status', 50)->default('active')->index(); // active, released, cancelled

            $table->timestamps();

            // Composite index for fast lookup
            $table->index(['sales_order_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_reservations');
    }
};
