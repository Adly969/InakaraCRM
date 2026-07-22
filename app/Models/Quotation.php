<?php

namespace App\Models;

use App\Enums\QuotationStatus;
use Database\Factories\QuotationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $reference_no
 * @property int|null $lead_id
 * @property int|null $customer_id
 * @property string $subject
 * @property int $revision
 * @property QuotationStatus $status
 * @property string $valid_until
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
    'lead_id',
    'customer_id',
    'subject',
    'revision',
    'status',
    'valid_until',
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
])]
class Quotation extends Model
{
    /** @use HasFactory<QuotationFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => QuotationStatus::class,
            'revision' => 'integer',
            'tax_rate' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    /**
     * Get the items of this quotation.
     *
     * @return HasMany<QuotationItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    /**
     * Get the customer associated with the quotation.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the lead associated with the quotation.
     *
     * @return BelongsTo<Lead, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the user assigned to this quotation.
     *
     * @return BelongsTo<User, $this>
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created this quotation.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this quotation.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this quotation.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
