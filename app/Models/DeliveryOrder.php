<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $reference_no
 * @property int $sales_order_id
 * @property int $warehouse_id
 * @property int $customer_id
 * @property int|null $company_id
 * @property int|null $branch_id
 * @property string $status
 * @property array $shipping_address_snapshot
 * @property array $billing_address_snapshot
 * @property string|null $notes
 * @property int $created_by
 * @property int|null $updated_by
 * @property int|null $approved_by
 * @property Carbon|null $approved_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'reference_no',
    'sales_order_id',
    'warehouse_id',
    'customer_id',
    'company_id',
    'branch_id',
    'status',
    'shipping_address_snapshot',
    'billing_address_snapshot',
    'notes',
    'created_by',
    'updated_by',
    'approved_by',
    'approved_at',
])]
class DeliveryOrder extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'shipping_address_snapshot' => 'json',
            'billing_address_snapshot' => 'json',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * Get the sales order this DO belongs to.
     *
     * @return BelongsTo<SalesOrder, $this>
     */
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    /**
     * Get the warehouse origin of this DO.
     *
     * @return BelongsTo<Warehouse, $this>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the customer this DO targets.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the line items under this DO.
     *
     * @return HasMany<DeliveryOrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(DeliveryOrderItem::class);
    }

    /**
     * Get the shipments dispatched under this DO.
     *
     * @return HasMany<Shipment, $this>
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    /**
     * Get the audit telemetry events for this DO.
     *
     * @return HasMany<DeliveryEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(DeliveryEvent::class);
    }

    /**
     * Get all invoices for this DO.
     *
     * @return HasMany<Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
