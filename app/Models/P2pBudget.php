<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_id',
    'cost_center_code',
    'fiscal_year',
    'allocated_amount',
    'reserved_amount',
    'committed_amount',
    'actual_spent_amount',
    'status',
    'version',
])]
class P2pBudget extends Model
{
    use HasTenantIsolation;
}
