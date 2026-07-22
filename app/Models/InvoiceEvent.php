<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'invoice_id',
        'event_type',
        'event_data',
        'ip_address',
        'user_agent',
        'created_by',
    ];

    protected $casts = [
        'event_data' => 'array',
        'created_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
