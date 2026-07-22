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
    'requisition_no',
    'requester_id',
    'cost_center_code',
    'type',
    'status',
    'total_amount',
    'version',
])]
class P2pRequisition extends Model
{
    use HasTenantIsolation, SoftDeletes;

    /**
     * Get lines for the requisition.
     *
     * @return HasMany<P2pRequisitionItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(P2pRequisitionItem::class, 'requisition_id');
    }
}
