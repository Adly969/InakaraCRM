<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'sales_order_item_id',
        'delivery_order_item_id',
        'sku',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'total_amount',
        'item_details_snapshot',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'item_details_snapshot' => 'array',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function salesOrderItem(): BelongsTo
    {
        return $this->belongsTo(SalesOrderItem::class);
    }

    public function deliveryOrderItem(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrderItem::class);
    }
}
