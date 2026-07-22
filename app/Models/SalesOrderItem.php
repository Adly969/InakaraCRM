<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $sales_order_id
 * @property string $description
 * @property float $quantity
 * @property string $unit
 * @property float $unit_price
 * @property float $total_price
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'sales_order_id',
    'description',
    'quantity',
    'unit',
    'unit_price',
    'total_price',
    'sort_order',
])]
class SalesOrderItem extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the sales order that owns this item.
     *
     * @return BelongsTo<SalesOrder, $this>
     */
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }
}
