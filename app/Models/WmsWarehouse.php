<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id',
    'branch_id',
    'code',
    'name',
    'type',
    'status',
    'version',
])]
class WmsWarehouse extends Model
{
    use HasTenantIsolation, SoftDeletes;

    /**
     * Get the locations associated with this warehouse.
     *
     * @return HasMany<WmsLocation, $this>
     */
    public function locations(): HasMany
    {
        return $this->hasMany(WmsLocation::class, 'warehouse_id');
    }
}
