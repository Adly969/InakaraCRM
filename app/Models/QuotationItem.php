<?php

namespace App\Models;

use Database\Factories\QuotationItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $quotation_id
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
    'quotation_id',
    'description',
    'quantity',
    'unit',
    'unit_price',
    'total_price',
    'sort_order',
])]
class QuotationItem extends Model
{
    /** @use HasFactory<QuotationItemFactory> */
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
     * Get the quotation that owns this item.
     *
     * @return BelongsTo<Quotation, $this>
     */
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }
}
