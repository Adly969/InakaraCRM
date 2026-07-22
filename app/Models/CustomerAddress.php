<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $customer_id
 * @property string $tenant_id
 * @property string $type
 * @property string $street_address
 * @property string $city
 * @property string $state_province
 * @property string $postal_code
 * @property string $country
 * @property bool $is_primary
 * @property int $version
 */
#[Fillable([
    'customer_id',
    'type',
    'street_address',
    'city',
    'state_province',
    'postal_code',
    'country',
    'is_primary',
    'version',
])]
class CustomerAddress extends Model
{
    use HasFactory, HasTenantIsolation;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    /**
     * Get the customer associated with the address.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
