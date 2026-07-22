<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $payment_id
 * @property int $invoice_id
 * @property string $amount
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class PaymentAllocation extends Model
{
    protected $fillable = [
        'payment_id',
        'invoice_id',
        'amount',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

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
     * @return HasMany<PaymentAllocationHistory, $this>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(PaymentAllocationHistory::class, 'payment_allocation_id');
    }
}
