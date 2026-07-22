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
        Schema::create('official_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->onDelete('restrict');
            $table->string('receipt_no', 100)->unique();
            $table->string('status', 50)->default('generated'); // generated, printed, emailed, voided
            $table->string('pdf_path', 255)->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamps();

            $table->index('payment_id', 'idx_receipts_payment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('official_receipts');
    }
};
