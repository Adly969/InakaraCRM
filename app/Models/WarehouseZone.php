<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseZone extends Model
{
    use HasTenantIsolation;
    use SoftDeletes;

    protected $table = 'warehouse_zones';

    protected $fillable = [
        'uuid',
        'tenant_id',
        'company_id',
        'warehouse_id',
        'code',
        'name',
        'zone_type',
        'is_temperature_controlled',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'is_temperature_controlled' => 'boolean',
        ];
    }

    /** @return BelongsTo<Warehouse, $this> */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /** @return HasMany<WarehouseBin, $this> */
    public function bins(): HasMany
    {
        return $this->hasMany(WarehouseBin::class, 'zone_id');
    }
}
