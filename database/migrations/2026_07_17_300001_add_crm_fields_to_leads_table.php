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
        Schema::table('leads', function (Blueprint $table) {
            $table->string('website')->nullable()->after('phone');
            $table->string('job_title')->nullable()->after('website');
            $table->string('campaign_source')->nullable()->after('source');
            $table->string('priority')->default('medium')->after('status');
            $table->string('heat_score')->default('cold')->after('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['website', 'job_title', 'campaign_source', 'priority', 'heat_score']);
        });
    }
};
