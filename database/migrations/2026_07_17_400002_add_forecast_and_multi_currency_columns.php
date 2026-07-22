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
        Schema::table('crm_pipeline_stages', function (Blueprint $table) {
            $table->string('forecast_category')->default('pipeline')->after('stage_sequence');
        });

        Schema::table('crm_opportunities', function (Blueprint $table) {
            $table->string('currency_code', 3)->default('IDR')->after('deal_value');
            $table->decimal('exchange_rate', 15, 6)->default(1.000000)->after('currency_code');
            $table->decimal('base_currency_amount', 15, 2)->default(0.00)->after('exchange_rate');
            $table->decimal('transaction_currency_amount', 15, 2)->default(0.00)->after('base_currency_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_pipeline_stages', function (Blueprint $table) {
            $table->dropColumn('forecast_category');
        });

        Schema::table('crm_opportunities', function (Blueprint $table) {
            $table->dropColumn([
                'currency_code',
                'exchange_rate',
                'base_currency_amount',
                'transaction_currency_amount',
            ]);
        });
    }
};
