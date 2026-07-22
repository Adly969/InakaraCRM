<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id',
    'branch_id',
    'vendor_id',
    'contract_id',
    'po_no',
    'type',
    'status',
    'currency_code',
    'exchange_rate',
    'total_amount',
    'version',
])]
class P2pPurchaseOrder extends Model
{
    use HasTenantIsolation, SoftDeletes;

    /**
     * Get vendor associated with this PO.
     *
     * @return BelongsTo<P2pVendor, $this>
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(P2pVendor::class, 'vendor_id');
    }

    /**
     * Get contract associated with this PO.
     *
     * @return BelongsTo<P2pContract, $this>
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(P2pContract::class, 'contract_id');
    }

    /**
     * Get items of this PO.
     *
     * @return HasMany<P2pPurchaseOrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(P2pPurchaseOrderItem::class, 'purchase_order_id');
    }
}
