<?php

namespace App\Models;

use App\Enums\StockAdjustmentStatus;
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
 * @property int $warehouse_id
 * @property Carbon $adjustment_date
 * @property StockAdjustmentStatus $status
 * @property string|null $notes
 * @property string|null $approval_note
 * @property int|null $approved_by
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'reference_no',
    'warehouse_id',
    'adjustment_date',
    'status',
    'notes',
    'approval_note',
    'approved_by',
    'created_by',
    'updated_by',
    'deleted_by',
])]
class StockAdjustment extends Model
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
            'status' => StockAdjustmentStatus::class,
            'adjustment_date' => 'date',
        ];
    }

    /**
     * Get the warehouse associated with this adjustment.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the items in this stock adjustment.
     */
    public function items(): HasMany
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }

    /**
     * Get the manager or owner who approved this adjustment.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who created this adjustment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated this adjustment.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this adjustment.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
