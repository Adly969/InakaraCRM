<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $sales_order_id
 * @property int $revision_number
 * @property array $change_log
 * @property int $created_by
 */
#[Fillable([
    'sales_order_id',
    'revision_number',
    'change_log',
    'created_by',
])]
class SalesOrderRevision extends Model
{
    use HasFactory;

    protected $table = 'sales_orders_revisions';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'change_log' => 'array',
        ];
    }

    /**
     * Get the sales order associated with the revision.
     *
     * @return BelongsTo<SalesOrder, $this>
     */
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    /**
     * Get the user who created this revision.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
