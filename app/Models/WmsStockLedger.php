<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'branch_id',
    'location_id',
    'sku',
    'batch_number',
    'serial_number',
    'quantity_current',
    'quantity_reserved',
    'avg_cost',
    'version',
])]
class WmsStockLedger extends Model
{
    use HasTenantIsolation;

    /**
     * Get the location for this ledger record.
     *
     * @return BelongsTo<WmsLocation, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(WmsLocation::class, 'location_id');
    }
}
