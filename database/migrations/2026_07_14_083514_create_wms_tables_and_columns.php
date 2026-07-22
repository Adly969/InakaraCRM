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
        // 1. WMS Warehouses
        Schema::create('wms_warehouses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('code', 50);
            $table->string('name', 100);
            $table->string('type', 30);
            $table->string('status', 30)->default('ACTIVE');
            $table->integer('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code']);
        });

        // 2. Hierarchical Location Topography
        Schema::create('wms_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('parent_location_id')->nullable();
            $table->string('type', 30);
            $table->string('code', 100);
            $table->decimal('max_weight', 12, 2)->default(0.00);
            $table->decimal('max_volume', 12, 2)->default(0.00);
            $table->decimal('current_weight', 12, 2)->default(0.00);
            $table->decimal('current_volume', 12, 2)->default(0.00);
            $table->string('status', 30)->default('ACTIVE');
            $table->integer('version')->default(1);
            $table->timestamps();

            $table->foreign('warehouse_id')->references('id')->on('wms_warehouses')->onDelete('cascade');
            $table->foreign('parent_location_id')->references('id')->on('wms_locations')->onDelete('set null');
            $table->unique(['warehouse_id', 'code']);
        });

        // 3. Stock Ledger Table
        Schema::create('wms_stock_ledgers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('location_id');
            $table->string('sku', 50);
            $table->string('batch_number', 100)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->decimal('quantity_current', 12, 2)->default(0.00);
            $table->decimal('quantity_reserved', 12, 2)->default(0.00);
            $table->decimal('avg_cost', 12, 2)->default(0.00);
            $table->integer('version')->default(1);
            $table->timestamps();

            $table->foreign('location_id')->references('id')->on('wms_locations')->onDelete('cascade');
        });

        // 4. Cost Layers Queue
        Schema::create('wms_cost_layers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('sku', 50);
            $table->timestamp('receipt_date');
            $table->decimal('quantity_initial', 12, 2);
            $table->decimal('quantity_remaining', 12, 2);
            $table->decimal('unit_cost', 12, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Tasks Engine Table
        Schema::create('wms_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('type', 30);
            $table->string('status', 30)->default('CREATED');
            $table->integer('priority')->default(10);
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('source_location_id')->nullable();
            $table->unsignedBigInteger('target_location_id')->nullable();
            $table->string('sku', 50);
            $table->decimal('quantity', 12, 2);
            $table->string('batch_number', 100)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->integer('version')->default(1);
            $table->timestamps();

            $table->foreign('source_location_id')->references('id')->on('wms_locations')->onDelete('set null');
            $table->foreign('target_location_id')->references('id')->on('wms_locations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wms_tasks');
        Schema::dropIfExists('wms_cost_layers');
        Schema::dropIfExists('wms_stock_ledgers');
        Schema::dropIfExists('wms_locations');
        Schema::dropIfExists('wms_warehouses');
    }
};
