<?php

namespace App\Models;

use App\Enums\InventoryTransactionType;
use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryTransaction extends Model
{
    use HasTenantIsolation;
    use SoftDeletes;

    protected $table = 'inventory_transactions';

    protected $fillable = [
        'uuid',
        'tenant_id',
        'company_id',
        'transaction_number',
        'transaction_type',
        'source_warehouse_id',
        'target_warehouse_id',
        'status',
        'notes',
        'version',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_type' => InventoryTransactionType::class,
        ];
    }

    /** @return BelongsTo<Warehouse, $this> */
    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    /** @return BelongsTo<Warehouse, $this> */
    public function targetWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'target_warehouse_id');
    }

    /** @return HasMany<InventoryTransactionItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(InventoryTransactionItem::class, 'transaction_id');
    }
}
