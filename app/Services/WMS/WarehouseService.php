<?php

namespace App\Services\WMS;

use App\Models\Warehouse;
use App\Models\WarehouseBin;
use App\Models\WarehouseZone;
use Illuminate\Support\Str;

class WarehouseService
{
    public function createWarehouse(array $data, string $tenantId, int $userId): Warehouse
    {
        return Warehouse::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'company_id' => $data['company_id'] ?? null,
            'branch_id' => $data['branch_id'] ?? null,
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'type' => $data['type'] ?? 'main',
            'is_default' => $data['is_default'] ?? false,
            'status' => $data['status'] ?? 'active',
            'address' => $data['address'] ?? null,
            'total_capacity_sqm' => $data['total_capacity_sqm'] ?? null,
            'manager_id' => $data['manager_id'] ?? null,
            'created_by' => $userId,
        ]);
    }

    public function createZone(Warehouse $warehouse, array $data, string $tenantId): WarehouseZone
    {
        return WarehouseZone::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'company_id' => $warehouse->company_id,
            'warehouse_id' => $warehouse->id,
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'zone_type' => $data['zone_type'] ?? 'storage',
            'is_temperature_controlled' => $data['is_temperature_controlled'] ?? false,
        ]);
    }

    public function createBin(WarehouseZone $zone, array $data, string $tenantId): WarehouseBin
    {
        return WarehouseBin::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'company_id' => $zone->company_id,
            'zone_id' => $zone->id,
            'bin_code' => strtoupper($data['bin_code']),
            'aisle' => $data['aisle'] ?? null,
            'rack' => $data['rack'] ?? null,
            'shelf' => $data['shelf'] ?? null,
            'bin' => $data['bin'] ?? null,
            'max_weight_kg' => $data['max_weight_kg'] ?? null,
            'max_volume_cbm' => $data['max_volume_cbm'] ?? null,
        ]);
    }
}
