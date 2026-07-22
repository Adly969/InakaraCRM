<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'demand_source_type',
    'demand_source_id',
    'supply_type',
    'supply_id',
    'pegged_quantity',
])]
class MfgApsSupplyPegging extends Model
{
    protected $table = 'mfg_aps_supply_pegging';
}
