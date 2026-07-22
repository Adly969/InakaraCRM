<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $tenant_id
 * @property int $customer_id
 * @property int|null $previous_owner_id
 * @property int|null $new_owner_id
 * @property string|null $reason
 * @property int|null $transferred_by
 * @property string $transferred_at
 */
#[Fillable([
    'customer_id',
    'previous_owner_id',
    'new_owner_id',
    'reason',
    'transferred_by',
    'transferred_at',
])]
class CustomerOwnerHistory extends Model
{
    use HasFactory, HasTenantIsolation;

    /**
     * Disable default model timestamps.
     */
    public $timestamps = false;

    /**
     * Get the customer associated with the history.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the previous owner user.
     *
     * @return BelongsTo<User, $this>
     */
    public function previousOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'previous_owner_id');
    }

    /**
     * Get the new owner user.
     *
     * @return BelongsTo<User, $this>
     */
    public function newOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'new_owner_id');
    }

    /**
     * Get the user who executed the transfer.
     *
     * @return BelongsTo<User, $this>
     */
    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }
}
