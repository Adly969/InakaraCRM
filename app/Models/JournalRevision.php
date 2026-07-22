<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_entry_id',
        'version_number',
        'changes',
        'reason',
        'editor_id',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'changes' => 'array',
    ];

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_id');
    }
}
