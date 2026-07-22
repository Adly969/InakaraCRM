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
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->nullable()->unique();
            $table->foreignId('sales_order_id')->nullable()->index()->constrained('sales_orders')->nullOnDelete();
            $table->foreignId('customer_id')->index()->constrained('customers')->restrictOnDelete();
            $table->string('subject');
            $table->string('status')->default('draft')->index();
            $table->string('priority')->default('normal')->index();
            $table->date('target_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->decimal('actual_hours', 8, 2)->nullable();
            $table->text('production_notes')->nullable();
            $table->text('cancellation_reason')->nullable();
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
        Schema::dropIfExists('production_orders');
    }
};
