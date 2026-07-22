<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialDimension extends Model
{
    use HasFactory, HasTenantIsolation;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'is_mandatory',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(FinancialDimensionValue::class);
    }
}
