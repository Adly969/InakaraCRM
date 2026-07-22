<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Tenant;
use App\Models\User;
use App\Services\WMS\ProductService;
use App\Services\WMS\WarehouseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WmsInventorySuiteTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Company $company;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['name' => 'Test Tenant WMS', 'slug' => 'test-tenant-wms', 'domain' => 'testwms']);
        $this->company = Company::create(['name' => 'Test Company WMS', 'tenant_id' => $this->tenant->id]);
        $branch = Branch::create(['name' => 'Test Branch WMS', 'company_id' => $this->company->id, 'tenant_id' => $this->tenant->id, 'code' => 'TB02']);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'branch_id' => $branch->id,
        ]);
        $this->actingAs($this->user);
    }

    public function test_can_create_warehouse_zone_and_bin(): void
    {
        /** @var WarehouseService $service */
        $service = app(WarehouseService::class);

        $warehouse = $service->createWarehouse([
            'company_id' => $this->company->id,
            'code' => 'WH-TEST-01',
            'name' => 'Main Test Warehouse',
            'type' => 'main',
        ], (string) $this->tenant->id, $this->user->id);

        $this->assertDatabaseHas('warehouses', [
            'code' => 'WH-TEST-01',
            'name' => 'Main Test Warehouse',
        ]);

        $zone = $service->createZone($warehouse, [
            'code' => 'Z-A',
            'name' => 'Storage Zone A',
        ], (string) $this->tenant->id);

        $bin = $service->createBin($zone, [
            'bin_code' => 'A-01-01',
        ], (string) $this->tenant->id);

        $this->assertDatabaseHas('warehouse_zones', ['code' => 'Z-A']);
        $this->assertDatabaseHas('warehouse_bins', ['bin_code' => 'A-01-01']);
    }

    public function test_can_create_product_with_auto_generated_sku(): void
    {
        /** @var ProductService $service */
        $service = app(ProductService::class);

        $product = $service->createProduct([
            'company_id' => $this->company->id,
            'name' => 'Executive Office Chair',
            'product_type' => 'finished_goods',
        ], (string) $this->tenant->id, $this->user->id);

        $this->assertNotNull($product->sku);
        $this->assertDatabaseHas('products', ['name' => 'Executive Office Chair']);
    }
}
