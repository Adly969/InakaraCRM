<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'branch_id',
    'rfq_no',
    'status',
    'close_date',
    'version',
])]
class P2pRfq extends Model
{
    use HasTenantIsolation;

    protected $table = 'p2p_rfqs';

    /**
     * Get attributes casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'close_date' => 'datetime',
        ];
    }

    /**
     * Get submitted bids associated with this RFQ.
     *
     * @return HasMany<P2pBid, $this>
     */
    public function bids(): HasMany
    {
        return $this->hasMany(P2pBid::class, 'rfq_id');
    }
}
