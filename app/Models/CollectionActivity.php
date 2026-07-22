<?php

namespace App\Models;

use App\Enums\CollectionActivityType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $customer_id
 * @property int|null $invoice_id
 * @property CollectionActivityType $activity_type
 * @property string $status
 * @property string|null $promise_amount
 * @property Carbon|null $promise_date
 * @property string|null $notes
 * @property Carbon|null $next_follow_up_date
 * @property int $assigned_to
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class CollectionActivity extends Model
{
    protected $fillable = [
        'customer_id',
        'invoice_id',
        'activity_type',
        'status',
        'promise_amount',
        'promise_date',
        'notes',
        'next_follow_up_date',
        'assigned_to',
        'created_by',
    ];

    protected $casts = [
        'activity_type' => CollectionActivityType::class,
        'promise_amount' => 'decimal:2',
        'promise_date' => 'date',
        'next_follow_up_date' => 'date',
    ];

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
