<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventOutbox extends Model
{
    protected $table = 'event_outbox';

    protected $fillable = [
        'company_id',
        'event_type',
        'payload',
        'idempotency_key',
        'is_dispatched',
        'dispatched_at',
    ];

    protected $casts = [
        'payload' => 'json',
        'is_dispatched' => 'boolean',
        'dispatched_at' => 'datetime',
    ];
}
