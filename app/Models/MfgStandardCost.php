<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_id',
    'sku',
    'standard_material_cost',
    'standard_labor_cost',
    'standard_machine_cost',
    'standard_overhead_cost',
    'is_active',
])]
class MfgStandardCost extends Model
{
    use HasTenantIsolation;
}
