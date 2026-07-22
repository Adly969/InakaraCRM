<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Inventory Balances
        if (! Schema::hasTable('inventory_balances')) {
            Schema::create('inventory_balances', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('tenant_id')->index();
                $table->foreignId('company_id')->nullable()->index();
                $table->foreignId('branch_id')->nullable()->index();
                $table->foreignId('warehouse_id')->constrained('warehouses');
                $table->foreignId('bin_id')->nullable()->constrained('warehouse_bins');
                $table->foreignId('product_id')->constrained('products');
                $table->foreignId('variant_id')->nullable()->constrained('product_variants');
                $table->string('batch_number', 100)->nullable()->index();
                $table->string('serial_number', 100)->nullable()->index();
                $table->decimal('quantity_on_hand', 15, 4)->default(0);
                $table->decimal('quantity_reserved', 15, 4)->default(0);
                $table->decimal('quantity_available', 15, 4)->default(0);
                $table->decimal('quantity_quarantine', 15, 4)->default(0);
                $table->date('expiry_date')->nullable()->index();
                $table->integer('version')->default(1);
                $table->timestamps();

                $table->index(['warehouse_id', 'bin_id', 'product_id']);
            });
        }

        // 2. Inventory Transactions Header
        if (! Schema::hasTable('inventory_transactions')) {
            Schema::create('inventory_transactions', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('tenant_id')->index();
                $table->foreignId('company_id')->nullable();
                $table->string('transaction_number', 50)->unique();
                $table->string('transaction_type', 30); // goods_receipt, goods_issue, transfer, adjustment, opname
                $table->foreignId('source_warehouse_id')->nullable()->constrained('warehouses');
                $table->foreignId('target_warehouse_id')->nullable()->constrained('warehouses');
                $table->string('status', 20)->default('draft'); // draft, approved, posted, cancelled
                $table->text('notes')->nullable();
                $table->integer('version')->default(1);
                $table->foreignId('created_by')->nullable();
                $table->foreignId('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 3. Inventory Transaction Items Detail
        if (! Schema::hasTable('inventory_transaction_items')) {
            Schema::create('inventory_transaction_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transaction_id')->constrained('inventory_transactions')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products');
                $table->foreignId('variant_id')->nullable()->constrained('product_variants');
                $table->foreignId('from_bin_id')->nullable()->constrained('warehouse_bins');
                $table->foreignId('to_bin_id')->nullable()->constrained('warehouse_bins');
                $table->string('batch_number', 100)->nullable();
                $table->string('serial_number', 100)->nullable();
                $table->decimal('quantity', 15, 4);
                $table->decimal('unit_cost', 15, 4)->default(0);
                $table->decimal('total_cost', 15, 4)->default(0);
                $table->timestamps();
            });
        }

        // 4. Stock Reservations
        if (! Schema::hasTable('stock_reservations')) {
            Schema::create('stock_reservations', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('tenant_id')->index();
                $table->foreignId('company_id')->nullable();
                $table->string('reservation_number', 50)->unique();
                $table->string('reference_type', 50); // sales_order, production_order, quotation
                $table->bigInteger('reference_id');
                $table->foreignId('product_id')->constrained('products');
                $table->foreignId('warehouse_id')->constrained('warehouses');
                $table->decimal('quantity_reserved', 15, 4);
                $table->string('status', 20)->default('active'); // active, expired, released, consumed
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamps();
            });
        }

        // 5. Stock Movements Log
        if (! Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('tenant_id')->index();
                $table->foreignId('product_id')->constrained('products');
                $table->foreignId('from_bin_id')->nullable()->constrained('warehouse_bins');
                $table->foreignId('to_bin_id')->nullable()->constrained('warehouse_bins');
                $table->decimal('quantity', 15, 4);
                $table->foreignId('performed_by')->nullable();
                $table->timestamps();
            });
        }

        // 6. Stock Adjustments & Opname Counts
        if (! Schema::hasTable('stock_adjustments')) {
            Schema::create('stock_adjustments', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('tenant_id')->index();
                $table->string('adjustment_number', 50)->unique();
                $table->foreignId('warehouse_id')->constrained('warehouses');
                $table->string('reason_code', 50); // damaged, lost, found, expired, audit
                $table->string('status', 20)->default('draft');
                $table->foreignId('requested_by')->nullable();
                $table->foreignId('approved_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('stock_counts')) {
            Schema::create('stock_counts', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('tenant_id')->index();
                $table->string('count_number', 50)->unique();
                $table->foreignId('warehouse_id')->constrained('warehouses');
                $table->string('count_type', 30)->default('cycle_count');
                $table->string('status', 20)->default('draft');
                $table->foreignId('counter_id')->nullable();
                $table->timestamps();
            });
        }

        // 7. Inventory Cost Layers (FIFO)
        if (! Schema::hasTable('inventory_cost_layers')) {
            Schema::create('inventory_cost_layers', function (Blueprint $table) {
                $table->id();
                $table->uuid('tenant_id')->index();
                $table->foreignId('warehouse_id')->constrained('warehouses');
                $table->foreignId('product_id')->constrained('products');
                $table->timestamp('received_date')->index();
                $table->decimal('original_quantity', 15, 4);
                $table->decimal('remaining_quantity', 15, 4);
                $table->decimal('unit_cost', 15, 4);
                $table->timestamps();
            });
        }

        // 8. Event Outbox Table for WMS Domain Events
        if (! Schema::hasTable('inventory_event_outbox')) {
            Schema::create('inventory_event_outbox', function (Blueprint $table) {
                $table->id();
                $table->uuid('event_id')->unique();
                $table->uuid('tenant_id')->index();
                $table->string('event_type', 100);
                $table->string('aggregate_type', 100);
                $table->bigInteger('aggregate_id');
                $table->json('payload');
                $table->boolean('is_processed')->default(false)->index();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_event_outbox');
        Schema::dropIfExists('inventory_cost_layers');
        Schema::dropIfExists('stock_counts');
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_reservations');
        Schema::dropIfExists('inventory_transaction_items');
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventory_balances');
    }
};
