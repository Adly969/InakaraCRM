<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id',
    'branch_id',
    'vendor_id',
    'contract_no',
    'type',
    'status',
    'start_date',
    'end_date',
    'total_value_limit',
    'released_value',
    'version',
])]
class P2pContract extends Model
{
    use HasTenantIsolation, SoftDeletes;

    /**
     * Get attributes casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Get vendor associated with this contract.
     *
     * @return BelongsTo<P2pVendor, $this>
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(P2pVendor::class, 'vendor_id');
    }
}
