<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_id',
    'branch_id',
    'production_order_id',
    'debit_amount',
    'credit_amount',
    'balance_amount',
])]
class MfgWipLedger extends Model
{
    use HasTenantIsolation;

    protected $table = 'mfg_wip_ledger';
}
