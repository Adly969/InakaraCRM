<?php

namespace App\Services;

use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class WarehouseService
{
    /**
     * Create a new warehouse.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Warehouse
    {
        return DB::transaction(function () use ($data) {
            if (! empty($data['is_default'])) {
                $this->clearDefaults();
            }

            return Warehouse::create($data);
        });
    }

    /**
     * Update an existing warehouse.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        return DB::transaction(function () use ($warehouse, $data) {
            if (! empty($data['is_default'])) {
                $this->clearDefaults($warehouse->id);
            }

            $warehouse->update($data);

            return $warehouse;
        });
    }

    /**
     * Reset is_default flag on all other warehouses.
     */
    protected function clearDefaults(?int $exceptId = null): void
    {
        $query = Warehouse::query();
        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }
        $query->update(['is_default' => false]);
    }
}
