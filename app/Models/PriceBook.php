<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $company_id
 * @property string $price_book_name
 * @property bool $is_active
 * @property bool $is_default
 * @property string $currency
 */
#[Fillable([
    'company_id',
    'price_book_name',
    'is_active',
    'is_default',
    'currency',
])]
class PriceBook extends Model
{
    use HasFactory;

    /**
     * Get the entries of this price book.
     *
     * @return HasMany<PriceBookEntry, $this>
     */
    public function entries(): HasMany
    {
        return $this->hasMany(PriceBookEntry::class);
    }
}
