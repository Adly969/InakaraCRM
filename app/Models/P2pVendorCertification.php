<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'vendor_id',
    'name',
    'certificate_no',
    'issuing_authority',
    'valid_from',
    'valid_to',
    'status',
])]
class P2pVendorCertification extends Model
{
    /**
     * Get attributes casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_to' => 'date',
        ];
    }

    /**
     * Get vendor this certification belongs to.
     *
     * @return BelongsTo<P2pVendor, $this>
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(P2pVendor::class, 'vendor_id');
    }
}
