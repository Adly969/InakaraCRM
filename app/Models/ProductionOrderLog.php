<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $production_order_id
 * @property string|null $status_from
 * @property string $status_to
 * @property string|null $note
 * @property int|null $created_by
 * @property Carbon|null $created_at
 */
#[Fillable([
    'production_order_id',
    'status_from',
    'status_to',
    'note',
    'created_by',
])]
class ProductionOrderLog extends Model
{
    /**
     * Disable the updated_at timestamp since this is an immutable log table.
     */
    const UPDATED_AT = null;

    /**
     * Get the production order associated with this log.
     *
     * @return BelongsTo<ProductionOrder, $this>
     */
    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    /**
     * Get the user who triggered/created this log.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
