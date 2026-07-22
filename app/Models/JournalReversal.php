<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalReversal extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_journal_id',
        'reversing_journal_id',
        'reversed_by',
        'reason',
    ];

    public function originalJournal(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'original_journal_id');
    }

    public function reversingJournal(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'reversing_journal_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }
}
