<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'warehouse_id',
    'parent_location_id',
    'type',
    'code',
    'max_weight',
    'max_volume',
    'current_weight',
    'current_volume',
    'status',
    'version',
])]
class WmsLocation extends Model
{
    use HasTenantIsolation;

    /**
     * Get the parent location.
     *
     * @return BelongsTo<WmsLocation, $this>
     */
    public function parentLocation(): BelongsTo
    {
        return $this->belongsTo(WmsLocation::class, 'parent_location_id');
    }

    /**
     * Get the child locations.
     *
     * @return HasMany<WmsLocation, $this>
     */
    public function childLocations(): HasMany
    {
        return $this->hasMany(WmsLocation::class, 'parent_location_id');
    }

    /**
     * Get the warehouse this location belongs to.
     *
     * @return BelongsTo<WmsWarehouse, $this>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(WmsWarehouse::class, 'warehouse_id');
    }
}
