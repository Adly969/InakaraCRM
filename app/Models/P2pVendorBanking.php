<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'vendor_id',
    'bank_name',
    'account_number',
    'account_name',
    'swift_code',
    'routing_code',
    'is_primary',
])]
class P2pVendorBanking extends Model
{
    protected $table = 'p2p_vendor_banking';

    /**
     * Get attributes casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'account_number' => 'encrypted',
        ];
    }

    /**
     * Get vendor this banking profile belongs to.
     *
     * @return BelongsTo<P2pVendor, $this>
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(P2pVendor::class, 'vendor_id');
    }
}
