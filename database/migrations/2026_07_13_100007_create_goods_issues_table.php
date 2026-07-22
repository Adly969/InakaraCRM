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
        Schema::create('goods_issues', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 100)->unique()->index();
            $table->foreignId('sales_order_id')->nullable()->index()->constrained('sales_orders')->restrictOnDelete();
            $table->foreignId('warehouse_id')->index()->constrained('warehouses')->restrictOnDelete();
            $table->date('issued_date');
            $table->string('status', 50)->default('draft')->index(); // draft, issued, cancelled

            $table->text('notes')->nullable();
            $table->text('remark')->nullable(); // For audit purposes

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
        Schema::dropIfExists('goods_issues');
    }
};
