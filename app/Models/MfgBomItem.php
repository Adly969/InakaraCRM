<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'bom_id',
    'sku',
    'quantity',
    'yield_factor',
])]
class MfgBomItem extends Model
{
    /**
     * Get parent BOM.
     *
     * @return BelongsTo<MfgBillOfMaterial, $this>
     */
    public function bom(): BelongsTo
    {
        return $this->belongsTo(MfgBillOfMaterial::class, 'bom_id');
    }
}
