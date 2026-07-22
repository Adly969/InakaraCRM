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
        Schema::create('delivery_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_order_id')->constrained('delivery_orders')->onDelete('cascade');
            $table->foreignId('shipment_id')->nullable()->constrained('shipments')->onDelete('set null');
            $table->string('event_type', 50);
            $table->json('event_data');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['delivery_order_id', 'created_at'], 'idx_de_do_event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_events');
    }
};
