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
 * @property int $customer_id
 * @property float $previous_limit
 * @property float $new_limit
 * @property int $adjusted_by
 * @property string|null $reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'customer_id',
    'previous_limit',
    'new_limit',
    'adjusted_by',
    'reason',
])]
class CreditHistory extends Model
{
    use HasTenantIsolation, HasUuids;

    protected $table = 'credit_history';

    protected $casts = [
        'previous_limit' => 'decimal:4',
        'new_limit' => 'decimal:4',
    ];

    /**
     * Get the customer that owns this credit history.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who adjusted the limit.
     *
     * @return BelongsTo<User, $this>
     */
    public function adjuster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}
