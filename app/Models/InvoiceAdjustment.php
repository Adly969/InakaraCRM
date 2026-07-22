<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceAdjustment extends Model
{
    protected $fillable = [
        'invoice_id',
        'type',
        'description',
        'amount',
        'is_taxable',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_taxable' => 'boolean',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
