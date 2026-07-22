<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'rfq_id',
    'vendor_id',
    'bid_no',
    'technical_score',
    'commercial_quote',
    'is_awarded',
])]
class P2pBid extends Model
{
    /**
     * Get attributes casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_awarded' => 'boolean',
        ];
    }

    /**
     * Get RFQ tender.
     *
     * @return BelongsTo<P2pRfq, $this>
     */
    public function rfq(): BelongsTo
    {
        return $this->belongsTo(P2pRfq::class, 'rfq_id');
    }

    /**
     * Get vendor who submitted the bid.
     *
     * @return BelongsTo<P2pVendor, $this>
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(P2pVendor::class, 'vendor_id');
    }
}
