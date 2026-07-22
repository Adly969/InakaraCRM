<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostingRuleVersion extends Model
{
    use HasTenantIsolation;

    protected $fillable = [
        'company_id',
        'branch_id',
        'event_type',
        'version',
        'priority',
        'status',
        'debit_account_id',
        'credit_account_id',
        'effective_from',
        'effective_until',
        'approved_by',
        'published_by',
        'published_at',
        'superseded_by',
    ];

    protected $casts = [
        'version' => 'integer',
        'priority' => 'integer',
        'effective_from' => 'datetime',
        'effective_until' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function debitAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'debit_account_id');
    }

    public function creditAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'credit_account_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
