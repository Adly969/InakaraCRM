<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseTaskItem extends Model
{
    protected $table = 'warehouse_task_items';

    protected $fillable = [
        'task_id',
        'product_id',
        'from_bin_id',
        'to_bin_id',
        'quantity_target',
        'quantity_scanned',
        'is_completed',
    ];

    protected function casts(): array
    {
        return [
            'quantity_target' => 'decimal:4',
            'quantity_scanned' => 'decimal:4',
            'is_completed' => 'boolean',
        ];
    }

    /** @return BelongsTo<WarehouseTask, $this> */
    public function task(): BelongsTo
    {
        return $this->belongsTo(WarehouseTask::class, 'task_id');
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
