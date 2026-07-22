<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostingFailure extends Model
{
    protected $fillable = [
        'job_id',
        'event_type',
        'failed_payload',
        'failure_reason',
        'stack_trace',
        'is_resolved',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'failed_payload' => 'json',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(PostingJob::class, 'job_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
