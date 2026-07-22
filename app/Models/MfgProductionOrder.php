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
    'bom_id',
    'production_no',
    'sku',
    'quantity_planned',
    'quantity_produced',
    'quantity_scrapped',
    'status',
    'scheduled_start',
    'scheduled_end',
    'version',
])]
class MfgProductionOrder extends Model
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
            'scheduled_start' => 'datetime',
            'scheduled_end' => 'datetime',
        ];
    }

    /**
     * Get BOM associated with order.
     *
     * @return BelongsTo<MfgBillOfMaterial, $this>
     */
    public function bom(): BelongsTo
    {
        return $this->belongsTo(MfgBillOfMaterial::class, 'bom_id');
    }

    /**
     * Get component reservations.
     *
     * @return HasMany<MfgMaterialReservation, $this>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(MfgMaterialReservation::class, 'production_order_id');
    }

    /**
     * Get routing operations queue steps.
     *
     * @return HasMany<MfgOperation, $this>
     */
    public function operations(): HasMany
    {
        return $this->hasMany(MfgOperation::class, 'production_order_id');
    }
}
