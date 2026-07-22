<?php

namespace App\Models;

use App\Enums\InventoryTransactionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $inventory_item_id
 * @property int $warehouse_id
 * @property InventoryTransactionType $transaction_type
 * @property string $reference_type
 * @property int $reference_id
 * @property string $movement_direction
 * @property float $quantity_before
 * @property float $quantity_change
 * @property float $quantity_after
 * @property float $reserved_before
 * @property float $reserved_after
 * @property float $cost_price
 * @property float $total_value_change
 * @property float $current_avg_cost_after
 * @property string|null $notes
 * @property int|null $created_by
 * @property Carbon|null $created_at
 */
#[Fillable([
    'inventory_item_id',
    'warehouse_id',
    'transaction_type',
    'reference_type',
    'reference_id',
    'movement_direction',
    'quantity_before',
    'quantity_change',
    'quantity_after',
    'reserved_before',
    'reserved_after',
    'cost_price',
    'total_value_change',
    'current_avg_cost_after',
    'notes',
    'created_by',
])]
class InventoryTransaction extends Model
{
    use HasFactory;

    /**
     * Disable standard updated_at column since ledger is insert-only.
     */
    public const UPDATED_AT = null;

    /**
     * Boot model and enforce absolute immutability.
     */
    protected static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            throw new \DomainException('Inventory transactions are immutable and cannot be updated.');
        });

        static::deleting(function ($model) {
            throw new \DomainException('Inventory transactions are immutable and cannot be deleted.');
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transaction_type' => InventoryTransactionType::class,
            'quantity_before' => 'decimal:2',
            'quantity_change' => 'decimal:2',
            'quantity_after' => 'decimal:2',
            'reserved_before' => 'decimal:2',
            'reserved_after' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'total_value_change' => 'decimal:2',
            'current_avg_cost_after' => 'decimal:2',
        ];
    }

    /**
     * Get the associated inventory item.
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Get the warehouse where this transaction occurred.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the user who posted this transaction.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
