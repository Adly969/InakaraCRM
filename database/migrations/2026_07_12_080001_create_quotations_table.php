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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->nullable()->unique();
            $table->foreignId('lead_id')->nullable()->index()->constrained('leads')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->index()->constrained('customers')->nullOnDelete();
            $table->string('subject');
            $table->unsignedInteger('revision')->default(1);
            $table->string('status')->default('draft')->index();
            $table->date('valid_until');
            $table->text('notes')->nullable();
            $table->string('currency', 3)->default('IDR');
            $table->decimal('tax_rate', 5, 2)->default(11.00);
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->foreignId('assigned_to')->nullable()->index()->constrained('users')->nullOnDelete();
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
        Schema::dropIfExists('quotations');
    }
};
