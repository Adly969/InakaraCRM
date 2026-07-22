<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property int $sales_order_id
 * @property string $promotion_code
 * @property float $discount_amount
 */
#[Fillable([
    'company_id',
    'sales_order_id',
    'promotion_code',
    'discount_amount',
])]
class SalesOrderPromotion extends Model
{
    use HasFactory;

    /**
     * Get the sales order associated with the promotion.
     *
     * @return BelongsTo<SalesOrder, $this>
     */
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }
}
