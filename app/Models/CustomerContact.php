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
 * @property string $first_name
 * @property string $last_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $mobile
 * @property string|null $whatsapp
 * @property string|null $position
 * @property string|null $department
 * @property bool $is_primary
 * @property string|null $notes
 * @property string $status
 * @property int $version
 * @property int|null $created_by
 * @property int|null $updated_by
 */
#[Fillable([
    'customer_id',
    'first_name',
    'last_name',
    'email',
    'phone',
    'mobile',
    'whatsapp',
    'position',
    'department',
    'is_primary',
    'notes',
    'status',
    'version',
    'created_by',
    'updated_by',
])]
class CustomerContact extends Model
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
     * Get the customer associated with the contact.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
