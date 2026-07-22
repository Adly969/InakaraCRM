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
    'bom_no',
    'sku',
    'description',
    'status',
    'version',
])]
class MfgBillOfMaterial extends Model
{
    use HasTenantIsolation, SoftDeletes;

    protected $table = 'mfg_boms';

    /**
     * Get component items for this BOM.
     *
     * @return HasMany<MfgBomItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(MfgBomItem::class, 'bom_id');
    }
}
