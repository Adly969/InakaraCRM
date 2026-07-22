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
        Schema::create('collection_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('restrict');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->string('activity_type', 50); // phone_call, whatsapp, email, visit, promise_to_pay, broken_promise
            $table->string('status', 50)->default('pending'); // pending, completed, broken
            $table->decimal('promise_amount', 15, 2)->nullable();
            $table->date('promise_date')->nullable();
            $table->text('notes')->nullable();
            $table->date('next_follow_up_date')->nullable();
            $table->foreignId('assigned_to')->constrained('users');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index('customer_id', 'idx_collection_cust');
            $table->index('invoice_id', 'idx_collection_invoice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_activities');
    }
};
