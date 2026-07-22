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
    'invoice_no',
    'vendor_id',
    'purchase_order_id',
    'amount_invoiced',
    'matching_status',
    'hold_reason_code',
    'version',
])]
class P2pInvoice extends Model
{
    use HasTenantIsolation, SoftDeletes;

    /**
     * Get vendor associated with this invoice.
     *
     * @return BelongsTo<P2pVendor, $this>
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(P2pVendor::class, 'vendor_id');
    }

    /**
     * Get purchase order associated with this invoice.
     *
     * @return BelongsTo<P2pPurchaseOrder, $this>
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(P2pPurchaseOrder::class, 'purchase_order_id');
    }

    /**
     * Get items of this invoice.
     *
     * @return HasMany<P2pInvoiceItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(P2pInvoiceItem::class, 'invoice_id', 'id');
    }
}
