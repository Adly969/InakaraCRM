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
        $tables = [
            'crm_pipeline_definitions',
            'crm_pipeline_stages',
            'crm_loss_reasons',
            'crm_opportunities',
            'crm_activities',
            'crm_stage_histories',
            'crm_tags',
            'crm_opportunity_competitors',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
                $table->unsignedBigInteger('branch_id')->nullable()->after('company_id')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'crm_opportunity_competitors',
            'crm_tags',
            'crm_stage_histories',
            'crm_activities',
            'crm_opportunities',
            'crm_loss_reasons',
            'crm_pipeline_stages',
            'crm_pipeline_definitions',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn(['company_id', 'branch_id']);
            });
        }
    }
};
