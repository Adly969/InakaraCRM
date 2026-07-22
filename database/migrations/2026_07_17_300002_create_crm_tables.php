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
        Schema::create('crm_pipeline_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('crm_pipeline_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pipeline_definition_id')->constrained('crm_pipeline_definitions')->cascadeOnDelete();
            $table->string('name', 50);
            $table->decimal('probability', 5, 2);
            $table->integer('stage_sequence');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['pipeline_definition_id', 'stage_sequence'], 'uid_pipeline_seq');
        });

        Schema::create('crm_loss_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('crm_opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->string('title', 150);
            $table->foreignId('pipeline_stage_id')->constrained('crm_pipeline_stages')->restrictOnDelete();
            $table->string('status')->default('qualification')->index();
            $table->decimal('deal_value', 15, 2)->default(0);
            $table->date('expected_close_date');
            $table->foreignId('loss_reason_id')->nullable()->constrained('crm_loss_reasons')->nullOnDelete();
            $table->text('loss_notes')->nullable();

            $table->foreignId('assigned_to')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'expected_close_date'], 'idx_opp_status_close');
        });

        Schema::create('crm_activities', function (Blueprint $table) {
            $table->id();
            $table->string('activity_type', 30);
            $table->string('subject', 150);
            $table->text('description')->nullable();
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->string('status', 20)->default('pending');

            $table->foreignId('lead_id')->nullable()->constrained('leads')->cascadeOnDelete();
            $table->foreignId('opportunity_id')->nullable()->constrained('crm_opportunities')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();

            $table->timestamps();

            $table->index(['lead_id', 'activity_type'], 'idx_activity_lead_type');
            $table->index(['opportunity_id', 'activity_type'], 'idx_activity_opp_type');
        });

        Schema::create('crm_stage_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained('crm_opportunities')->cascadeOnDelete();
            $table->foreignId('from_stage_id')->constrained('crm_pipeline_stages')->restrictOnDelete();
            $table->foreignId('to_stage_id')->constrained('crm_pipeline_stages')->restrictOnDelete();
            $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
            $table->integer('duration_in_seconds')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->index('opportunity_id', 'idx_stage_hist_opp');
        });

        Schema::create('crm_opportunity_competitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained('crm_opportunities')->cascadeOnDelete();
            $table->string('competitor_name', 100);
            $table->string('strengths')->nullable();
            $table->string('weaknesses')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('crm_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('color_hex', 7)->default('#CBD5E1');
            $table->timestamps();

            $table->unique('name', 'uid_tag_name');
        });

        Schema::create('crm_taggables', function (Blueprint $table) {
            $table->foreignId('tag_id')->constrained('crm_tags')->cascadeOnDelete();
            $table->string('taggable_type', 100);
            $table->unsignedBigInteger('taggable_id');

            $table->primary(['tag_id', 'taggable_type', 'taggable_id'], 'pk_taggable');
            $table->index(['taggable_type', 'taggable_id'], 'idx_taggable_morph');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_taggables');
        Schema::dropIfExists('crm_tags');
        Schema::dropIfExists('crm_opportunity_competitors');
        Schema::dropIfExists('crm_stage_histories');
        Schema::dropIfExists('crm_activities');
        Schema::dropIfExists('crm_opportunities');
        Schema::dropIfExists('crm_loss_reasons');
        Schema::dropIfExists('crm_pipeline_stages');
        Schema::dropIfExists('crm_pipeline_definitions');
    }
};
