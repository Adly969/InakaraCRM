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
        Schema::create('payment_allocation_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_allocation_id');
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('restrict');
            $table->decimal('before_amount', 15, 2);
            $table->decimal('after_amount', 15, 2);
            $table->foreignId('modified_by')->constrained('users');
            $table->string('reason', 255);
            $table->timestamp('created_at')->useCurrent();

            $table->index('payment_id', 'idx_allocation_hist_payment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_allocation_histories');
    }
};
