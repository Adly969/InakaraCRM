<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReservation extends Model
{
    use HasTenantIsolation;

    protected $table = 'stock_reservations';

    protected $fillable = [
        'uuid',
        'tenant_id',
        'company_id',
        'reservation_number',
        'reference_type',
        'reference_id',
        'product_id',
        'warehouse_id',
        'quantity_reserved',
        'status',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReservationStatus::class,
            'quantity_reserved' => 'decimal:4',
            'expires_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return BelongsTo<Warehouse, $this> */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
