<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference_no',
        'sales_order_id',
        'delivery_order_id',
        'customer_id',
        'company_id',
        'branch_id',
        'status',
        'invoice_date',
        'due_date',
        'payment_term_code',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'adjustment_amount',
        'total_amount',
        'outstanding_balance',
        'currency',
        'exchange_rate',
        'billing_address_snapshot',
        'shipping_address_snapshot',
        'notes',
        'void_reason',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'status' => InvoiceStatus::class,
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'adjustment_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'billing_address_snapshot' => 'array',
        'shipping_address_snapshot' => 'array',
        'approved_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(InvoiceAdjustment::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(InvoiceEvent::class);
    }

    /**
     * @return HasMany<PaymentAllocation, $this>
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    /**
     * @return HasManyThrough<Payment, PaymentAllocation, $this>
     */
    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(
            Payment::class,
            PaymentAllocation::class,
            'invoice_id', // Foreign key on payment_allocations table
            'id',         // Foreign key on payments table
            'id',         // Local key on invoices table
            'payment_id'  // Local key on payment_allocations table
        );
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
