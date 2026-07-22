<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    use HasFactory, HasTenantIsolation;

    protected $fillable = [
        'company_id',
        'branch_id',
        'ledger_id',
        'journal_number',
        'journal_type',
        'transaction_date',
        'reverse_on_date',
        'current_version',
        'created_by',
        'approved_by',
        'posted_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'reverse_on_date' => 'date',
        'current_version' => 'integer',
    ];

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(JournalRevision::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}
