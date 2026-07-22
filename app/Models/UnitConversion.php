<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitConversion extends Model
{
    use HasTenantIsolation;

    protected $table = 'unit_conversions';

    protected $fillable = [
        'tenant_id',
        'from_unit_id',
        'to_unit_id',
        'conversion_factor',
        'purpose',
    ];

    protected function casts(): array
    {
        return [
            'conversion_factor' => 'decimal:6',
        ];
    }

    /** @return BelongsTo<Unit, $this> */
    public function fromUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'from_unit_id');
    }

    /** @return BelongsTo<Unit, $this> */
    public function toUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'to_unit_id');
    }
}
