<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Warehouse Tasks & Task Items
        if (! Schema::hasTable('warehouse_tasks')) {
            Schema::create('warehouse_tasks', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('tenant_id')->index();
                $table->foreignId('company_id')->nullable();
                $table->string('task_number', 50)->unique();
                $table->string('task_type', 30); // put_away, picking, packing, shipping, receiving, internal_transfer, cycle_count
                $table->string('status', 20)->default('draft'); // draft, assigned, in_progress, completed, cancelled
                $table->string('priority', 20)->default('medium');
                $table->foreignId('warehouse_id')->constrained('warehouses');
                $table->foreignId('assigned_operator_id')->nullable()->constrained('users');
                $table->dateTime('due_date')->nullable();
                $table->integer('estimated_duration_minutes')->nullable();
                $table->integer('actual_duration_minutes')->nullable();
                $table->dateTime('started_at')->nullable();
                $table->dateTime('completed_at')->nullable();
                $table->integer('version')->default(1);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('warehouse_task_items')) {
            Schema::create('warehouse_task_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('warehouse_tasks')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products');
                $table->foreignId('from_bin_id')->nullable()->constrained('warehouse_bins');
                $table->foreignId('to_bin_id')->nullable()->constrained('warehouse_bins');
                $table->decimal('quantity_target', 15, 4);
                $table->decimal('quantity_scanned', 15, 4)->default(0);
                $table->boolean('is_completed')->default(false);
                $table->timestamps();
            });
        }

        // 2. Barcode Scan Logs & Scanner Sessions
        if (! Schema::hasTable('scanner_sessions')) {
            Schema::create('scanner_sessions', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('tenant_id')->index();
                $table->foreignId('user_id')->constrained('users');
                $table->string('device_type', 30); // mobile_terminal, camera, usb_hid, bluetooth
                $table->string('device_serial', 100)->nullable();
                $table->string('status', 20)->default('active');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('barcode_scan_logs')) {
            Schema::create('barcode_scan_logs', function (Blueprint $table) {
                $table->id();
                $table->uuid('tenant_id')->index();
                $table->foreignId('session_id')->nullable()->constrained('scanner_sessions');
                $table->foreignId('user_id')->constrained('users');
                $table->string('scanned_code', 150)->index();
                $table->string('barcode_type', 30); // gs1, ean13, upc, code128, qr
                $table->boolean('is_valid')->default(true);
                $table->string('validation_message', 255)->nullable();
                $table->timestamp('scanned_at')->index();
            });
        }

        // 3. Inventory Period Closings
        if (! Schema::hasTable('inventory_period_closings')) {
            Schema::create('inventory_period_closings', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('tenant_id')->index();
                $table->foreignId('company_id')->nullable();
                $table->foreignId('warehouse_id')->constrained('warehouses');
                $table->date('period_date')->index(); // First day of month
                $table->string('period_type', 20)->default('month');
                $table->string('status', 20)->default('closed');
                $table->timestamp('closed_at');
                $table->foreignId('closed_by')->constrained('users');
                $table->timestamps();
            });
        }

        // 4. Approval Matrices & Requests
        if (! Schema::hasTable('approval_matrices')) {
            Schema::create('approval_matrices', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('tenant_id')->index();
                $table->string('approval_type', 50); // adjustment, transfer, scrap
                $table->decimal('min_amount', 15, 2)->default(0);
                $table->decimal('max_amount', 15, 2)->nullable();
                $table->integer('required_level')->default(1);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('approval_requests')) {
            Schema::create('approval_requests', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('tenant_id')->index();
                $table->foreignId('matrix_id')->nullable()->constrained('approval_matrices');
                $table->string('approvable_type', 100);
                $table->bigInteger('approvable_id');
                $table->string('status', 20)->default('pending');
                $table->foreignId('requested_by')->constrained('users');
                $table->foreignId('actioned_by')->nullable()->constrained('users');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 5. Document Number Sequences (Atomic Generator)
        if (! Schema::hasTable('document_number_sequences')) {
            Schema::create('document_number_sequences', function (Blueprint $table) {
                $table->id();
                $table->uuid('tenant_id')->index();
                $table->foreignId('company_id')->nullable();
                $table->string('prefix', 10); // GRN, GIN, TRF, ADJ, OPN, CNT, RES, MOV
                $table->integer('fiscal_year');
                $table->integer('current_sequence')->default(0);
                $table->timestamps();

                $table->unique(['tenant_id', 'company_id', 'prefix', 'fiscal_year'], 'doc_seq_unique');
            });
        }

        // 6. Inventory Feature Flags (Tenant level)
        if (! Schema::hasTable('inventory_feature_flags')) {
            Schema::create('inventory_feature_flags', function (Blueprint $table) {
                $table->id();
                $table->uuid('tenant_id')->index();
                $table->string('feature_key', 50);
                $table->boolean('is_enabled')->default(true);
                $table->timestamps();

                $table->unique(['tenant_id', 'feature_key']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_feature_flags');
        Schema::dropIfExists('document_number_sequences');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_matrices');
        Schema::dropIfExists('inventory_period_closings');
        Schema::dropIfExists('barcode_scan_logs');
        Schema::dropIfExists('scanner_sessions');
        Schema::dropIfExists('warehouse_task_items');
        Schema::dropIfExists('warehouse_tasks');
    }
};
