<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Activity Comments
        Schema::create('crm_activity_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('crm_activities')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->text('body');
            $table->foreignId('parent_id')->nullable()->constrained('crm_activity_comments')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Activity Attachments
        Schema::create('crm_activity_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('crm_activities')->cascadeOnDelete();
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type', 100);
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });

        // Tasks
        Schema::create('crm_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('priority', 10)->default('medium');
            $table->date('due_date');
            $table->time('due_time')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('opportunity_id')->nullable()->constrained('crm_opportunities')->nullOnDelete();
            $table->foreignId('assigned_to')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('parent_task_id')->nullable()->constrained('crm_tasks')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamp('reminder_at')->nullable();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->integer('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['assigned_to', 'status'], 'idx_task_assigned_status');
            $table->index('due_date', 'idx_task_due_date');
            $table->index('customer_id', 'idx_task_customer');
            $table->index('opportunity_id', 'idx_task_opportunity');
        });

        // Task Checklists
        Schema::create('crm_task_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('crm_tasks')->cascadeOnDelete();
            $table->string('label', 200);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Calendar Events
        Schema::create('crm_calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('event_type', 30);
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->boolean('all_day')->default(false);
            $table->string('location', 255)->nullable();
            $table->string('color', 7)->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_rule', 100)->nullable();
            $table->foreignId('activity_id')->nullable()->constrained('crm_activities')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('opportunity_id')->nullable()->constrained('crm_opportunities')->nullOnDelete();
            $table->foreignId('organizer_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 20)->default('confirmed');
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->integer('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index('organizer_id', 'idx_calendar_organizer');
            $table->index(['start_at', 'end_at'], 'idx_calendar_start_end');
            $table->index('customer_id', 'idx_calendar_customer');
        });

        // Meeting Attendees
        Schema::create('crm_meeting_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_event_id')->constrained('crm_calendar_events')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('external_name', 100)->nullable();
            $table->string('external_email', 150)->nullable();
            $table->string('rsvp_status', 20)->default('pending');
            $table->timestamp('created_at')->useCurrent();
        });

        // Documents
        Schema::create('crm_document_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->foreignId('parent_id')->nullable()->constrained('crm_document_folders')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnDelete();
            $table->foreignId('opportunity_id')->nullable()->constrained('crm_opportunities')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('crm_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('document_type', 30);
            $table->foreignId('folder_id')->nullable()->constrained('crm_document_folders')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('opportunity_id')->nullable()->constrained('crm_opportunities')->nullOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->nullOnDelete();
            $table->boolean('is_pinned')->default(false);
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->integer('version')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('crm_document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('crm_documents')->cascadeOnDelete();
            $table->integer('version_number');
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type', 100);
            $table->string('checksum', 64)->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->string('change_notes', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // Reminders (polymorphic)
        Schema::create('crm_reminders', function (Blueprint $table) {
            $table->id();
            $table->string('remindable_type', 100);
            $table->unsignedBigInteger('remindable_id');
            $table->timestamp('remind_at');
            $table->string('message', 500)->nullable();
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['is_sent', 'remind_at'], 'idx_reminder_fire');
            $table->index(['remindable_type', 'remindable_id'], 'idx_reminder_morph');
        });

        // Polymorphic Comments
        Schema::create('crm_comments', function (Blueprint $table) {
            $table->id();
            $table->string('commentable_type', 100);
            $table->unsignedBigInteger('commentable_id');
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->text('body');
            $table->foreignId('parent_id')->nullable()->constrained('crm_comments')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['commentable_type', 'commentable_id'], 'idx_comment_morph');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_comments');
        Schema::dropIfExists('crm_reminders');
        Schema::dropIfExists('crm_document_versions');
        Schema::dropIfExists('crm_documents');
        Schema::dropIfExists('crm_document_folders');
        Schema::dropIfExists('crm_meeting_attendees');
        Schema::dropIfExists('crm_calendar_events');
        Schema::dropIfExists('crm_task_checklists');
        Schema::dropIfExists('crm_tasks');
        Schema::dropIfExists('crm_activity_attachments');
        Schema::dropIfExists('crm_activity_comments');
    }
};
