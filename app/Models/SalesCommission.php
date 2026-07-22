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
 * @property int $salesperson_id
 * @property float $commission_rate
 * @property float $commission_amount
 * @property string $status
 */
#[Fillable([
    'company_id',
    'sales_order_id',
    'salesperson_id',
    'commission_rate',
    'commission_amount',
    'status',
])]
class SalesCommission extends Model
{
    use HasFactory;

    /**
     * Get the sales order associated with the commission.
     *
     * @return BelongsTo<SalesOrder, $this>
     */
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    /**
     * Get the salesperson associated with this commission.
     *
     * @return BelongsTo<User, $this>
     */
    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }
}
