<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostingRuleAudit extends Model
{
    protected $fillable = [
        'rule_id',
        'action',
        'before_state',
        'after_state',
        'changed_by',
        'approved_by',
        'ip_address',
        'user_agent',
        'correlation_id',
    ];

    protected $casts = [
        'before_state' => 'json',
        'after_state' => 'json',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(PostingRuleVersion::class, 'rule_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
