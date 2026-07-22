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
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->index()->constrained('inventory_items')->restrictOnDelete();
            $table->foreignId('warehouse_id')->index()->constrained('warehouses')->restrictOnDelete();
            $table->string('transaction_type', 50)->index(); // receipt, issue, adjustment_in, adjustment_out
            $table->string('reference_type'); // Model class
            $table->bigInteger('reference_id'); // Model ID
            $table->string('movement_direction', 10); // in, out, none

            $table->decimal('quantity_before', 15, 2);
            $table->decimal('quantity_change', 15, 2);
            $table->decimal('quantity_after', 15, 2);

            $table->decimal('reserved_before', 15, 2);
            $table->decimal('reserved_after', 15, 2);

            $table->decimal('cost_price', 15, 2)->default(0.00);
            $table->decimal('total_value_change', 15, 2)->default(0.00);
            $table->decimal('current_avg_cost_after', 15, 2)->default(0.00);

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();

            // Composite index for fast audit and ordering
            $table->index(['inventory_item_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
