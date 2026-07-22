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
 * @property int $warehouse_id
 * @property int|null $product_id
 * @property string $sku
 * @property string $name
 * @property string|null $description
 * @property float $quantity_current
 * @property float $quantity_reserved
 * @property string $unit
 * @property float $avg_cost_price
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'warehouse_id',
    'product_id',
    'sku',
    'name',
    'description',
    'quantity_current',
    'quantity_reserved',
    'unit',
    'avg_cost_price',
    'created_by',
    'updated_by',
    'deleted_by',
])]
class InventoryItem extends Model
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
            'quantity_current' => 'decimal:2',
            'quantity_reserved' => 'decimal:2',
            'avg_cost_price' => 'decimal:2',
        ];
    }

    /**
     * Get the warehouse where this inventory item belongs.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the transactions associated with this inventory item.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    /**
     * Get the reservations associated with this inventory item.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(InventoryReservation::class);
    }

    /**
     * Get the user who created this inventory item.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this inventory item.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this inventory item.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
