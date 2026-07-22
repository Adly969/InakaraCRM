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
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('restrict');
            $table->decimal('amount', 15, 2);
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->unique(['payment_id', 'invoice_id'], 'uq_payment_invoice');
            $table->index('payment_id', 'idx_allocations_payment');
            $table->index('invoice_id', 'idx_allocations_invoice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
    }
};
