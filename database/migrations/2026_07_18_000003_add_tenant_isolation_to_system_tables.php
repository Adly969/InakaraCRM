<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Feature flags table
        if (! Schema::hasTable('tenant_feature_flags')) {
            Schema::create('tenant_feature_flags', function (Blueprint $table) {
                $table->uuid('tenant_id');
                $table->string('feature_code', 100);
                $table->boolean('is_enabled')->default(true);
                $table->primary(['tenant_id', 'feature_code']);
                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        // 2. Add tenant_id to users
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'tenant_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->uuid('tenant_id')->nullable()->after('id');
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        // 3. Rename crm_audit_logs to audit_logs and adjust columns
        if (Schema::hasTable('crm_audit_logs') && ! Schema::hasTable('audit_logs')) {
            Schema::rename('crm_audit_logs', 'audit_logs');
        }

        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                if (! Schema::hasColumn('audit_logs', 'tenant_id')) {
                    $table->uuid('tenant_id')->nullable()->after('id');
                    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                }
                if (! Schema::hasColumn('audit_logs', 'action')) {
                    $table->string('action', 100)->nullable()->after('user_id'); // maps to blueprint
                }
                // Try catch to ignore index if it already exists
                try {
                    $table->index(['tenant_id', 'auditable_type', 'auditable_id'], 'idx_audit_tenant_entity');
                } catch (Exception $e) {
                    // Already exists
                }
            });
        }

        // 4. Update Spatie tables for teams mode
        $tableNames = config('permission.table_names');
        if (! empty($tableNames)) {
            Schema::disableForeignKeyConstraints();

            if (Schema::hasTable($tableNames['roles']) && ! Schema::hasColumn($tableNames['roles'], 'team_id')) {
                Schema::table($tableNames['roles'], function (Blueprint $table) {
                    $table->uuid('team_id')->nullable()->after('id');
                    $table->index('team_id');
                    // Remove existing unique
                    try {
                        $table->dropUnique('roles_name_guard_name_unique');
                    } catch (Exception $e) {
                        // Ignore
                    }
                    $table->unique(['team_id', 'name', 'guard_name']);
                });
            }

            if (Schema::hasTable($tableNames['model_has_roles'])) {
                if (! Schema::hasColumn($tableNames['model_has_roles'], 'team_id')) {
                    Schema::table($tableNames['model_has_roles'], function (Blueprint $table) {
                        $table->uuid('team_id')->nullable();
                        $table->index('team_id');
                    });
                }
            }

            if (Schema::hasTable($tableNames['model_has_permissions'])) {
                if (! Schema::hasColumn($tableNames['model_has_permissions'], 'team_id')) {
                    Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) {
                        $table->uuid('team_id')->nullable();
                        $table->index('team_id');
                    });
                }
            }

            Schema::enableForeignKeyConstraints();
        }

        // 5. Add tenant_id to other existing tables
        $tablesToIsolate = [
            'leads', 'opportunities', 'customers', 'quotations', 'sales_orders',
            'production_orders', 'warehouses', 'inventory_items', 'goods_receipts',
            'goods_issues', 'stock_adjustments', 'delivery_orders', 'shipments',
            'invoices', 'payments', 'ledgers',
        ];

        foreach ($tablesToIsolate as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->uuid('tenant_id')->nullable()->after('id');
                    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                    $table->index('tenant_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tablesToIsolate = [
            'leads', 'opportunities', 'customers', 'quotations', 'sales_orders',
            'production_orders', 'warehouses', 'inventory_items', 'goods_receipts',
            'goods_issues', 'stock_adjustments', 'delivery_orders', 'shipments',
            'invoices', 'payments', 'ledgers',
        ];

        foreach ($tablesToIsolate as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('tenant_id');
                });
            }
        }

        $tableNames = config('permission.table_names');
        if (! empty($tableNames)) {
            Schema::disableForeignKeyConstraints();

            if (Schema::hasTable($tableNames['model_has_permissions']) && Schema::hasColumn($tableNames['model_has_permissions'], 'team_id')) {
                Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) {
                    $table->dropColumn('team_id');
                });
            }

            if (Schema::hasTable($tableNames['model_has_roles']) && Schema::hasColumn($tableNames['model_has_roles'], 'team_id')) {
                Schema::table($tableNames['model_has_roles'], function (Blueprint $table) {
                    $table->dropColumn('team_id');
                });
            }

            if (Schema::hasTable($tableNames['roles']) && Schema::hasColumn($tableNames['roles'], 'team_id')) {
                Schema::table($tableNames['roles'], function (Blueprint $table) {
                    $table->dropUnique('roles_team_id_name_guard_name_unique');
                    $table->dropColumn('team_id');
                    $table->unique(['name', 'guard_name'], 'roles_name_guard_name_unique');
                });
            }

            Schema::enableForeignKeyConstraints();
        }

        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropColumn(['tenant_id', 'action']);
            });
            Schema::rename('audit_logs', 'crm_audit_logs');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::dropIfExists('tenant_feature_flags');
    }
};
