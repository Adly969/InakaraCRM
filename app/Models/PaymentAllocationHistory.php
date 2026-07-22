<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $payment_allocation_id
 * @property int $payment_id
 * @property int $invoice_id
 * @property string $before_amount
 * @property string $after_amount
 * @property int $modified_by
 * @property string $reason
 * @property Carbon|null $created_at
 */
class PaymentAllocationHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'payment_allocation_id',
        'payment_id',
        'invoice_id',
        'before_amount',
        'after_amount',
        'modified_by',
        'reason',
    ];

    protected $casts = [
        'before_amount' => 'decimal:2',
        'after_amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<PaymentAllocation, $this>
     */
    public function allocation(): BelongsTo
    {
        return $this->belongsTo(PaymentAllocation::class, 'payment_allocation_id');
    }

    /**
     * @return BelongsTo<Payment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
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
    public function modifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
}
