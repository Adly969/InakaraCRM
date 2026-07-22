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
        // Add version, first_name, and last_name columns to existing leads table if they don't exist
        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                if (! Schema::hasColumn('leads', 'version')) {
                    $table->integer('version')->default(1)->after('status');
                }
                if (! Schema::hasColumn('leads', 'first_name')) {
                    $table->string('first_name')->nullable()->after('name');
                }
                if (! Schema::hasColumn('leads', 'last_name')) {
                    $table->string('last_name')->nullable()->after('first_name');
                }
            });
        }

        // Add version and parent_id columns to existing customers table if they don't exist
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                if (! Schema::hasColumn('customers', 'version')) {
                    $table->integer('version')->default(1)->after('status');
                }
                if (! Schema::hasColumn('customers', 'parent_id')) {
                    $table->unsignedBigInteger('parent_id')->nullable()->after('status')->index();
                }
            });
        }

        // 1. Customer Contacts Table
        if (! Schema::hasTable('customer_contacts')) {
            Schema::create('customer_contacts', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('customer_id')->index();
                $table->uuid('tenant_id')->index();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('email')->nullable()->index();
                $table->string('phone')->nullable();
                $table->string('mobile')->nullable();
                $table->string('whatsapp')->nullable();
                $table->string('position')->nullable();
                $table->string('department')->nullable();
                $table->boolean('is_primary')->default(false);
                $table->text('notes')->nullable();
                $table->string('status')->default('active'); // active, inactive
                $table->integer('version')->default(1);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->index(['customer_id', 'is_primary']);
            });
        }

        // 2. Customer Addresses Table
        if (! Schema::hasTable('customer_addresses')) {
            Schema::create('customer_addresses', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('customer_id')->index();
                $table->uuid('tenant_id')->index();
                $table->string('type'); // office, billing, shipping, warehouse, branch, custom
                $table->text('street_address');
                $table->string('city');
                $table->string('state_province');
                $table->string('postal_code');
                $table->string('country')->default('Indonesia');
                $table->boolean('is_primary')->default(false);
                $table->integer('version')->default(1);
                $table->timestamps();

                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->index(['customer_id', 'is_primary']);
            });
        }

        // 3. Customer Tags Table
        if (! Schema::hasTable('customer_tags')) {
            Schema::create('customer_tags', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->uuid('tenant_id')->index();
                $table->string('name');
                $table->string('color')->default('#6B7280');
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->unique(['tenant_id', 'name']);
            });
        }

        // 4. Customer Taggables (Many-to-Many Pivot)
        if (! Schema::hasTable('customer_taggables')) {
            Schema::create('customer_taggables', function (Blueprint $table) {
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('tag_id');

                $table->primary(['customer_id', 'tag_id']);
                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
                $table->foreign('tag_id')->references('id')->on('customer_tags')->onDelete('cascade');
            });
        }

        // 5. Customer Owner Histories Table
        if (! Schema::hasTable('customer_owner_histories')) {
            Schema::create('customer_owner_histories', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->uuid('tenant_id')->index();
                $table->unsignedBigInteger('customer_id')->index();
                $table->unsignedBigInteger('previous_owner_id')->nullable()->index();
                $table->unsignedBigInteger('new_owner_id')->nullable()->index();
                $table->text('reason')->nullable();
                $table->unsignedBigInteger('transferred_by')->nullable()->index();
                $table->timestamp('transferred_at');

                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
                $table->foreign('previous_owner_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('new_owner_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('transferred_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        // 6. Activities Table
        if (! Schema::hasTable('activities')) {
            Schema::create('activities', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('customer_id')->nullable()->index();
                $table->unsignedBigInteger('lead_id')->nullable()->index();
                $table->uuid('tenant_id')->index();
                $table->string('type'); // call, meeting, visit, email, whatsapp, note, system
                $table->string('subject');
                $table->text('description')->nullable();
                $table->timestamp('occurred_at')->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();

                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
                $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        // 7. Activity Attachments Table
        if (! Schema::hasTable('activity_attachments')) {
            Schema::create('activity_attachments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->uuid('tenant_id')->index();
                $table->unsignedBigInteger('activity_id')->index();
                $table->string('disk')->default('local');
                $table->string('path');
                $table->string('filename');
                $table->string('mime_type');
                $table->unsignedBigInteger('size');
                $table->unsignedBigInteger('uploaded_by')->nullable()->index();
                $table->timestamp('created_at');

                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
                $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        // 8. Follow-Ups Table
        if (! Schema::hasTable('follow_ups')) {
            Schema::create('follow_ups', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('customer_id')->nullable()->index();
                $table->uuid('tenant_id')->index();
                $table->unsignedBigInteger('activity_id')->nullable()->index();
                $table->string('title');
                $table->text('description')->nullable();
                $table->timestamp('due_date')->index();
                $table->string('priority')->default('medium')->index(); // low, medium, high
                $table->string('status')->default('pending')->index(); // pending, completed, cancelled
                $table->unsignedBigInteger('assigned_to')->nullable()->index();
                $table->timestamp('completed_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->foreign('activity_id')->references('id')->on('activities')->onDelete('set null');
                $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            });
        }

        // 9. Custom Field Definitions Table
        if (! Schema::hasTable('custom_field_definitions')) {
            Schema::create('custom_field_definitions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->uuid('tenant_id')->index();
                $table->string('field_name');
                $table->string('field_label');
                $table->string('field_type'); // text, number, date, select
                $table->json('options')->nullable();
                $table->boolean('is_required')->default(false);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->unique(['tenant_id', 'field_name']);
            });
        }

        // 10. Customer Custom Field Values Table
        if (! Schema::hasTable('customer_custom_field_values')) {
            Schema::create('customer_custom_field_values', function (Blueprint $table) {
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('definition_id');
                $table->text('value')->nullable();

                $table->primary(['customer_id', 'definition_id']);
                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
                $table->foreign('definition_id')->references('id')->on('custom_field_definitions')->onDelete('cascade');
            });
        }

        // 11. CRM Event Outbox Table
        if (! Schema::hasTable('crm_event_outbox')) {
            Schema::create('crm_event_outbox', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->uuid('tenant_id')->index();
                $table->string('event_type');
                $table->json('payload');
                $table->timestamp('processed_at')->nullable()->index();
                $table->timestamps();
            });
        }

        // 12. CRM Dashboard Projections Table
        if (! Schema::hasTable('crm_dashboard_projections')) {
            Schema::create('crm_dashboard_projections', function (Blueprint $table) {
                $table->uuid('tenant_id');
                $table->string('metric_key');
                $table->decimal('metric_value', 15, 2);
                $table->timestamp('last_updated_at');

                $table->primary(['tenant_id', 'metric_key']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_dashboard_projections');
        Schema::dropIfExists('crm_event_outbox');
        Schema::dropIfExists('customer_custom_field_values');
        Schema::dropIfExists('custom_field_definitions');
        Schema::dropIfExists('follow_ups');
        Schema::dropIfExists('activity_attachments');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('customer_owner_histories');
        Schema::dropIfExists('customer_taggables');
        Schema::dropIfExists('customer_tags');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customer_contacts');

        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                if (Schema::hasColumn('customers', 'version')) {
                    $table->dropColumn('version');
                }
            });
        }

        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                if (Schema::hasColumn('leads', 'version')) {
                    $table->dropColumn('version');
                }
            });
        }
    }
};
