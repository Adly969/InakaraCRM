<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBalance extends Model
{
    use HasTenantIsolation;

    protected $table = 'inventory_balances';

    protected $fillable = [
        'uuid',
        'tenant_id',
        'company_id',
        'branch_id',
        'warehouse_id',
        'bin_id',
        'product_id',
        'variant_id',
        'batch_number',
        'serial_number',
        'quantity_on_hand',
        'quantity_reserved',
        'quantity_available',
        'quantity_quarantine',
        'expiry_date',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'quantity_on_hand' => 'decimal:4',
            'quantity_reserved' => 'decimal:4',
            'quantity_available' => 'decimal:4',
            'quantity_quarantine' => 'decimal:4',
            'expiry_date' => 'date',
        ];
    }

    /** @return BelongsTo<Warehouse, $this> */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /** @return BelongsTo<WarehouseBin, $this> */
    public function bin(): BelongsTo
    {
        return $this->belongsTo(WarehouseBin::class, 'bin_id');
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
