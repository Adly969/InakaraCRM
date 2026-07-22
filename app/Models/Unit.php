<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasTenantIsolation;

    protected $table = 'units';

    protected $fillable = [
        'uuid',
        'tenant_id',
        'code',
        'name',
    ];

    /** @return HasMany<UnitConversion, $this> */
    public function conversionsFrom(): HasMany
    {
        return $this->hasMany(UnitConversion::class, 'from_unit_id');
    }
}
