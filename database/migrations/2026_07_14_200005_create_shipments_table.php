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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_order_id')->constrained('delivery_orders')->onDelete('cascade');
            $table->string('reference_no', 100)->unique();
            $table->foreignId('carrier_id')->nullable()->constrained('carriers');
            $table->foreignId('driver_id')->nullable()->constrained('drivers');
            $table->string('courier_type', 50);
            $table->string('tracking_number', 100)->nullable();
            $table->string('status', 50)->default('pending_dispatch');
            $table->decimal('estimated_cost', 15, 2)->default(0.00);
            $table->decimal('actual_cost', 15, 2)->default(0.00);
            $table->string('currency', 3)->default('IDR');
            $table->decimal('exchange_rate', 15, 6)->default(1.000000);
            $table->date('estimated_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index('delivery_order_id', 'idx_shp_do');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
