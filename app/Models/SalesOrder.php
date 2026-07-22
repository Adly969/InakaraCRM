<?php

namespace App\Models;

use App\Enums\SalesOrderStatus;
use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $reference_no
 * @property int|null $quotation_id
 * @property int $customer_id
 * @property string $subject
 * @property SalesOrderStatus $status
 * @property string|null $delivery_terms
 * @property string|null $cancellation_reason
 * @property string|null $notes
 * @property string $currency
 * @property float $tax_rate
 * @property float $subtotal
 * @property float $tax_amount
 * @property float $total_amount
 * @property int|null $assigned_to
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'reference_no',
    'quotation_id',
    'customer_id',
    'subject',
    'status',
    'delivery_terms',
    'cancellation_reason',
    'notes',
    'currency',
    'tax_rate',
    'subtotal',
    'tax_amount',
    'total_amount',
    'assigned_to',
    'created_by',
    'updated_by',
    'deleted_by',
    'company_id',
    'branch_id',
    'credit_hold_status',
    'credit_hold_released_by',
    'credit_hold_released_at',
    'credit_hold_override_reason',
])]
class SalesOrder extends Model
{
    use HasFactory, HasTenantIsolation, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SalesOrderStatus::class,
            'tax_rate' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    /**
     * Get the items of this sales order.
     *
     * @return HasMany<SalesOrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    /**
     * Get the production order associated with the sales order.
     *
     * @return HasOne<ProductionOrder, $this>
     */
    public function productionOrder(): HasOne
    {
        return $this->hasOne(ProductionOrder::class);
    }

    /**
     * Get the customer associated with the sales order.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the quotation associated with the sales order.
     *
     * @return BelongsTo<Quotation, $this>
     */
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    /**
     * Get the user assigned to this sales order.
     *
     * @return BelongsTo<User, $this>
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created this sales order.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this sales order.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this sales order.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get all invoices for this sales order.
     *
     * @return HasMany<Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get all revisions for this sales order.
     *
     * @return HasMany<SalesOrderRevision, $this>
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(SalesOrderRevision::class, 'sales_order_id');
    }

    /**
     * Get all promotions applied to this sales order.
     *
     * @return HasMany<SalesOrderPromotion, $this>
     */
    public function promotions(): HasMany
    {
        return $this->hasMany(SalesOrderPromotion::class);
    }

    /**
     * Get all commissions for this sales order.
     *
     * @return HasMany<SalesCommission, $this>
     */
    public function commissions(): HasMany
    {
        return $this->hasMany(SalesCommission::class);
    }

    /**
     * Get the user who released the credit hold.
     *
     * @return BelongsTo<User, $this>
     */
    public function creditHoldReleasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'credit_hold_released_by');
    }
}
