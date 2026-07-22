<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_id',
    'branch_id',
    'proposal_no',
    'status',
    'total_payout_amount',
    'version',
])]
class P2pPaymentProposal extends Model
{
    use HasTenantIsolation;
}
