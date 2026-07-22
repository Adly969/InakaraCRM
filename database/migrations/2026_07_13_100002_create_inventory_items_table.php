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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->index()->constrained('warehouses')->restrictOnDelete();
            $table->bigInteger('product_id')->nullable()->index(); // Nullable, FK to future product catalog
            $table->string('sku', 100)->index();
            $table->string('name');
            $table->text('description')->nullable();

            $table->decimal('quantity_current', 15, 2)->default(0.00);
            $table->decimal('quantity_reserved', 15, 2)->default(0.00);
            $table->string('unit', 50)->default('pcs');
            $table->decimal('avg_cost_price', 15, 2)->default(0.00);

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
        Schema::dropIfExists('inventory_items');
    }
};
