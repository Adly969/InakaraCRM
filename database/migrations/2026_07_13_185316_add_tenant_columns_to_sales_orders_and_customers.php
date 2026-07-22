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
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->default(1)->after('id');
            $table->unsignedBigInteger('branch_id')->default(1)->after('company_id');

            $table->index(['company_id', 'branch_id']);
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->default(1)->after('id');
            $table->unsignedBigInteger('branch_id')->default(1)->after('company_id');

            $table->index(['company_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'branch_id']);
            $table->dropColumn(['company_id', 'branch_id']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'branch_id']);
            $table->dropColumn(['company_id', 'branch_id']);
        });
    }
};
