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
    'code',
    'name',
    'category',
    'qualification_status',
    'tax_number',
    'currency_code',
    'payment_terms_code',
    'risk_level',
    'esg_score',
    'version',
])]
class P2pVendor extends Model
{
    use HasTenantIsolation, SoftDeletes;

    /**
     * Get certifications for the vendor.
     *
     * @return HasMany<P2pVendorCertification, $this>
     */
    public function certifications(): HasMany
    {
        return $this->hasMany(P2pVendorCertification::class, 'vendor_id');
    }

    /**
     * Get banking profiles for the vendor.
     *
     * @return HasMany<P2pVendorBanking, $this>
     */
    public function banking(): HasMany
    {
        return $this->hasMany(P2pVendorBanking::class, 'vendor_id');
    }

    /**
     * Get contracts for the vendor.
     *
     * @return HasMany<P2pContract, $this>
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(P2pContract::class, 'vendor_id');
    }
}
