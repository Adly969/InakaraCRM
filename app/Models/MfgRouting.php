<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'branch_id',
    'sku',
    'name',
    'is_active',
])]
class MfgRouting extends Model
{
    use HasTenantIsolation;

    /**
     * Get operations routing steps.
     *
     * @return HasMany<MfgRoutingStep, $this>
     */
    public function steps(): HasMany
    {
        return $this->hasMany(MfgRoutingStep::class, 'routing_id');
    }
}
