<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $price_book_id
 * @property string $sku
 * @property float $unit_price
 * @property float $min_quantity
 */
#[Fillable([
    'price_book_id',
    'sku',
    'unit_price',
    'min_quantity',
])]
class PriceBookEntry extends Model
{
    use HasFactory;

    /**
     * Get the price book associated with this entry.
     *
     * @return BelongsTo<PriceBook, $this>
     */
    public function priceBook(): BelongsTo
    {
        return $this->belongsTo(PriceBook::class);
    }
}
