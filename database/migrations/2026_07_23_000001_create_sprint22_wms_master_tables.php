<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Warehouses Table (ALTER if exists, or CREATE if not)
        if (! Schema::hasTable('warehouses')) {
            Schema::create('warehouses', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('tenant_id')->index();
                $table->foreignId('company_id')->nullable()->index();
                $table->foreignId('branch_id')->nullable()->index();
                $table->string('code', 30)->index();
                $table->string('name', 150);
                $table->string('type', 30)->default('main');
                $table->boolean('is_default')->default(false);
                $table->string('status', 20)->default('active');
                $table->text('address')->nullable();
                $table->decimal('total_capacity_sqm', 12, 2)->nullable();
                $table->foreignId('manager_id')->nullable();
                $table->integer('version')->default(1);
                $table->foreignId('created_by')->nullable();
                $table->foreignId('updated_by')->nullable();
                $table->foreignId('deleted_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            Schema::table('warehouses', function (Blueprint $table) {
                if (! Schema::hasColumn('warehouses', 'uuid')) {
                    $table->uuid('uuid')->nullable();
                }
                if (! Schema::hasColumn('warehouses', 'tenant_id')) {
                    $table->uuid('tenant_id')->nullable();
                }
                if (! Schema::hasColumn('warehouses', 'company_id')) {
                    $table->foreignId('company_id')->nullable();
                }
                if (! Schema::hasColumn('warehouses', 'branch_id')) {
                    $table->foreignId('branch_id')->nullable();
                }
                if (! Schema::hasColumn('warehouses', 'total_capacity_sqm')) {
                    $table->decimal('total_capacity_sqm', 12, 2)->nullable();
                }
                if (! Schema::hasColumn('warehouses', 'version')) {
                    $table->integer('version')->default(1);
                }
            });
        }

        // 2. Warehouse Zones
        if (! Schema::hasTable('warehouse_zones')) {
            Schema::create('warehouse_zones', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('tenant_id')->index();
                $table->foreignId('company_id')->nullable();
                $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->string('code', 30);
                $table->string('name', 100);
                $table->string('zone_type', 30)->default('storage');
                $table->boolean('is_temperature_controlled')->default(false);
                $table->integer('version')->default(1);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 3. Warehouse Bins
        if (! Schema::hasTable('warehouse_bins')) {
            Schema::create('warehouse_bins', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('tenant_id')->index();
                $table->foreignId('company_id')->nullable();
                $table->foreignId('zone_id')->constrained('warehouse_zones')->cascadeOnDelete();
                $table->string('bin_code', 50)->index();
                $table->string('aisle', 10)->nullable();
                $table->string('rack', 10)->nullable();
                $table->string('shelf', 10)->nullable();
                $table->string('bin', 10)->nullable();
                $table->decimal('max_weight_kg', 10, 2)->nullable();
                $table->decimal('max_volume_cbm', 10, 2)->nullable();
                $table->boolean('is_locked')->default(false);
                $table->integer('version')->default(1);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 4. Product Categories & Brands
        if (! Schema::hasTable('product_categories')) {
            Schema::create('product_categories', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('tenant_id')->index();
                $table->string('name', 100);
                $table->foreignId('parent_id')->nullable()->constrained('product_categories')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('product_brands')) {
            Schema::create('product_brands', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('tenant_id')->index();
                $table->string('name', 100);
                $table->timestamps();
            });
        }

        // 5. Units (UOM) & Conversions
        if (! Schema::hasTable('units')) {
            Schema::create('units', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('tenant_id')->index();
                $table->string('code', 20)->index();
                $table->string('name', 50);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('unit_conversions')) {
            Schema::create('unit_conversions', function (Blueprint $table) {
                $table->id();
                $table->uuid('tenant_id')->index();
                $table->foreignId('from_unit_id')->constrained('units');
                $table->foreignId('to_unit_id')->constrained('units');
                $table->decimal('conversion_factor', 15, 6);
                $table->string('purpose', 30)->default('general'); // purchase, sales, inventory, manufacturing
                $table->timestamps();
            });
        }

        // 6. Products
        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('tenant_id')->index();
                $table->foreignId('company_id')->nullable();
                $table->string('sku', 50)->index();
                $table->string('barcode', 100)->nullable()->index();
                $table->string('name', 200);
                $table->string('product_type', 30)->default('finished_goods');
                $table->foreignId('category_id')->nullable()->constrained('product_categories');
                $table->foreignId('brand_id')->nullable()->constrained('product_brands');
                $table->foreignId('primary_uom_id')->nullable()->constrained('units');
                $table->decimal('safety_stock', 15, 4)->default(0);
                $table->decimal('reorder_point', 15, 4)->default(0);
                $table->integer('lead_time_days')->default(0);
                $table->string('abc_classification', 1)->default('C');
                $table->boolean('is_batch_tracked')->default(false);
                $table->boolean('is_serial_tracked')->default(false);
                $table->integer('version')->default(1);
                $table->foreignId('created_by')->nullable();
                $table->foreignId('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 7. Product Variants
        if (! Schema::hasTable('product_variants')) {
            Schema::create('product_variants', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('tenant_id')->index();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('variant_sku', 50)->index();
                $table->string('variant_name', 150);
                $table->json('attributes_json')->nullable();
                $table->timestamps();
            });
        }

        // 8. Product Digital Assets & Attachments
        if (! Schema::hasTable('product_digital_assets')) {
            Schema::create('product_digital_assets', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('tenant_id')->index();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('asset_type', 30); // cad, drawing, manual, 3d_model, video, gallery
                $table->string('title', 150);
                $table->string('file_name', 255);
                $table->string('file_path', 500);
                $table->integer('file_size');
                $table->string('mime_type', 100);
                $table->integer('version_number')->default(1);
                $table->foreignId('uploaded_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('product_attachments')) {
            Schema::create('product_attachments', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('tenant_id')->index();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('category', 30); // pdf, excel, image, certificate, safety_sheet
                $table->string('title', 150);
                $table->string('file_name', 255);
                $table->string('file_path', 500);
                $table->integer('file_size');
                $table->string('mime_type', 100);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attachments');
        Schema::dropIfExists('product_digital_assets');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('unit_conversions');
        Schema::dropIfExists('units');
        Schema::dropIfExists('product_brands');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('warehouse_bins');
        Schema::dropIfExists('warehouse_zones');
    }
};
