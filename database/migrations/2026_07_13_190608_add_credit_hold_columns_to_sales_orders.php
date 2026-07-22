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
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('credit_hold_status', 30)->default('none')->after('status');
            $table->unsignedBigInteger('credit_hold_released_by')->nullable()->after('credit_hold_status');
            $table->timestamp('credit_hold_released_at')->nullable()->after('credit_hold_released_by');
            $table->text('credit_hold_override_reason')->nullable()->after('credit_hold_released_at');

            $table->foreign('credit_hold_released_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign(['credit_hold_released_by']);
            $table->dropColumn([
                'credit_hold_status',
                'credit_hold_released_by',
                'credit_hold_released_at',
                'credit_hold_override_reason',
            ]);
        });
    }
};
