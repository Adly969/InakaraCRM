<?php

namespace App\Models;

use App\Enums\GoodsIssueStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $reference_no
 * @property int|null $sales_order_id
 * @property int $warehouse_id
 * @property Carbon $issued_date
 * @property GoodsIssueStatus $status
 * @property string|null $notes
 * @property string|null $remark
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'reference_no',
    'sales_order_id',
    'warehouse_id',
    'issued_date',
    'status',
    'notes',
    'remark',
    'created_by',
    'updated_by',
    'deleted_by',
])]
class GoodsIssue extends Model
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
            'status' => GoodsIssueStatus::class,
            'issued_date' => 'date',
        ];
    }

    /**
     * Get the warehouse associated with this issue.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the source sales order associated with this issue.
     */
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    /**
     * Get the items in this goods issue.
     */
    public function items(): HasMany
    {
        return $this->hasMany(GoodsIssueItem::class);
    }

    /**
     * Get the user who created this issue.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated this issue.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this issue.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
