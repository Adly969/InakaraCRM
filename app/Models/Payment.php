<?php

namespace App\Models;

use App\Enums\PaymentMethodType;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $reference_no
 * @property int $customer_id
 * @property int|null $company_id
 * @property int|null $branch_id
 * @property PaymentStatus $status
 * @property Carbon $payment_date
 * @property PaymentMethodType $payment_method
 * @property string $amount
 * @property string $allocated_amount
 * @property string $unallocated_amount
 * @property string $base_currency
 * @property string $transaction_currency
 * @property string $exchange_rate
 * @property string|null $exchange_rate_source
 * @property Carbon|null $exchange_rate_date
 * @property bool $exchange_rate_locked
 * @property string|null $exchange_rate_notes
 * @property string|null $bank_name
 * @property string|null $bank_account_no
 * @property string|null $cheque_no
 * @property string|null $transaction_ref
 * @property string|null $notes
 * @property string|null $cancellation_reason
 * @property string|null $reversal_reason
 * @property int|null $submitted_by
 * @property Carbon|null $submitted_at
 * @property int|null $verified_by
 * @property Carbon|null $verified_at
 * @property int|null $approved_by
 * @property Carbon|null $approved_at
 * @property int|null $posted_by
 * @property Carbon|null $posted_at
 * @property int|null $reversed_by
 * @property Carbon|null $reversed_at
 * @property int $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference_no',
        'customer_id',
        'company_id',
        'branch_id',
        'status',
        'payment_date',
        'payment_method',
        'amount',
        'allocated_amount',
        'unallocated_amount',
        'base_currency',
        'transaction_currency',
        'exchange_rate',
        'exchange_rate_source',
        'exchange_rate_date',
        'exchange_rate_locked',
        'exchange_rate_notes',
        'bank_name',
        'bank_account_no',
        'cheque_no',
        'transaction_ref',
        'notes',
        'cancellation_reason',
        'reversal_reason',
        'submitted_by',
        'submitted_at',
        'verified_by',
        'verified_at',
        'approved_by',
        'approved_at',
        'posted_by',
        'posted_at',
        'reversed_by',
        'reversed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => PaymentStatus::class,
        'payment_method' => PaymentMethodType::class,
        'payment_date' => 'date',
        'exchange_rate_date' => 'date',
        'amount' => 'decimal:2',
        'allocated_amount' => 'decimal:2',
        'unallocated_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'exchange_rate_locked' => 'boolean',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'posted_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return HasMany<PaymentAllocation, $this>
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    /**
     * @return HasMany<PaymentAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(PaymentAttachment::class);
    }

    /**
     * @return HasMany<PaymentEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(PaymentEvent::class);
    }

    /**
     * @return HasMany<PaymentAllocationHistory, $this>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(PaymentAllocationHistory::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reverser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }
}
