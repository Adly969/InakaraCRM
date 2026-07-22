<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $tenant_id
 * @property int $payment_allocation_id
 * @property string $action
 * @property int $actor_id
 * @property string|null $reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'payment_allocation_id',
    'action',
    'actor_id',
    'reason',
])]
class AllocationLog extends Model
{
    use HasTenantIsolation, HasUuids;

    /**
     * Get the allocation linked to this log.
     *
     * @return BelongsTo<PaymentAllocation, $this>
     */
    public function allocation(): BelongsTo
    {
        return $this->belongsTo(PaymentAllocation::class, 'payment_allocation_id');
    }

    /**
     * Get the user who executed the allocation edit.
     *
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
