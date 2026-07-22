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
 * @property int|null $sales_order_id
 * @property float $requested_amount
 * @property string $decision
 * @property int|null $decision_by
 * @property string|null $justification
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'customer_id',
    'sales_order_id',
    'requested_amount',
    'decision',
    'decision_by',
    'justification',
])]
class CreditReview extends Model
{
    use HasTenantIsolation, HasUuids;

    protected $casts = [
        'requested_amount' => 'decimal:4',
    ];

    /**
     * Get the customer that owns this credit review.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the sales order under review.
     *
     * @return BelongsTo<SalesOrder, $this>
     */
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    /**
     * Get the user who approved or rejected the override.
     *
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decision_by');
    }
}
