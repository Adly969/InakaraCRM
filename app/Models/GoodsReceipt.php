<?php

namespace App\Models;

use App\Enums\GoodsReceiptStatus;
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
 * @property int|null $production_order_id
 * @property int $warehouse_id
 * @property Carbon $received_date
 * @property GoodsReceiptStatus $status
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
    'production_order_id',
    'warehouse_id',
    'received_date',
    'status',
    'notes',
    'remark',
    'created_by',
    'updated_by',
    'deleted_by',
])]
class GoodsReceipt extends Model
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
            'status' => GoodsReceiptStatus::class,
            'received_date' => 'date',
        ];
    }

    /**
     * Get the warehouse associated with this receipt.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the source production order associated with this receipt.
     */
    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    /**
     * Get the items in this goods receipt.
     */
    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    /**
     * Get the user who created this receipt.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated this receipt.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this receipt.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
