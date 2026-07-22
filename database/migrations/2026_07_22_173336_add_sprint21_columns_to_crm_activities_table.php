<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_activities', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('opportunity_id')->constrained('customers')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->string('outcome', 30)->nullable()->after('status');
            $table->string('priority', 10)->default('medium')->after('outcome');
            $table->string('location', 255)->nullable()->after('priority');
            $table->integer('duration_minutes')->nullable()->after('location');
            $table->timestamp('reminder_at')->nullable()->after('duration_minutes');
            $table->boolean('is_recurring')->default(false)->after('reminder_at');
            $table->string('recurrence_rule', 100)->nullable()->after('is_recurring');
            $table->integer('version')->default(1)->after('recurrence_rule');
            $table->foreignId('updated_by')->nullable()->after('version')->constrained('users')->nullOnDelete();
            $table->softDeletes();

            $table->index('customer_id', 'idx_activity_customer');
            $table->index('assigned_to', 'idx_activity_assigned');
            $table->index('reminder_at', 'idx_activity_reminder');
            $table->index('start_time', 'idx_activity_start_time');
        });
    }

    public function down(): void
    {
        Schema::table('crm_activities', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['assigned_to']);
            $table->dropForeign(['updated_by']);
            $table->dropIndex('idx_activity_customer');
            $table->dropIndex('idx_activity_assigned');
            $table->dropIndex('idx_activity_reminder');
            $table->dropIndex('idx_activity_start_time');
            $table->dropSoftDeletes();
            $table->dropColumn([
                'customer_id', 'assigned_to', 'outcome', 'priority', 'location',
                'duration_minutes', 'reminder_at', 'is_recurring', 'recurrence_rule',
                'version', 'updated_by',
            ]);
        });
    }
};
