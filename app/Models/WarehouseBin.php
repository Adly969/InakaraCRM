<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseBin extends Model
{
    use HasTenantIsolation;
    use SoftDeletes;

    protected $table = 'warehouse_bins';

    protected $fillable = [
        'uuid',
        'tenant_id',
        'company_id',
        'zone_id',
        'bin_code',
        'aisle',
        'rack',
        'shelf',
        'bin',
        'max_weight_kg',
        'max_volume_cbm',
        'is_locked',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'is_locked' => 'boolean',
            'max_weight_kg' => 'decimal:2',
            'max_volume_cbm' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<WarehouseZone, $this> */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(WarehouseZone::class, 'zone_id');
    }

    /** @return HasMany<InventoryBalance, $this> */
    public function balances(): HasMany
    {
        return $this->hasMany(InventoryBalance::class, 'bin_id');
    }
}
