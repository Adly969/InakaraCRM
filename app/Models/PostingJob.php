<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PostingJob extends Model
{
    protected $fillable = [
        'event_id',
        'batch_id',
        'status',
        'retry_count',
        'max_retries',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'retry_count' => 'integer',
        'max_retries' => 'integer',
        'processed_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(FinancialEvent::class, 'event_id');
    }

    public function failure(): HasOne
    {
        return $this->hasOne(PostingFailure::class, 'job_id');
    }
}
