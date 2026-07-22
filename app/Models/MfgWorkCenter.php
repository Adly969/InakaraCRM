<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'branch_id',
    'code',
    'name',
    'efficiency_rate',
    'hourly_labor_rate',
    'hourly_machine_rate',
    'status',
])]
class MfgWorkCenter extends Model
{
    use HasTenantIsolation;

    /**
     * Get active machinery assigned.
     *
     * @return HasMany<MfgMachine, $this>
     */
    public function machines(): HasMany
    {
        return $this->hasMany(MfgMachine::class, 'work_center_id');
    }
}
