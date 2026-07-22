<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransactionItem extends Model
{
    protected $table = 'inventory_transaction_items';

    protected $fillable = [
        'transaction_id',
        'product_id',
        'variant_id',
        'from_bin_id',
        'to_bin_id',
        'batch_number',
        'serial_number',
        'quantity',
        'unit_cost',
        'total_cost',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:4',
        ];
    }

    /** @return BelongsTo<InventoryTransaction, $this> */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(InventoryTransaction::class, 'transaction_id');
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
