<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ledger extends Model
{
    use HasFactory, HasTenantIsolation;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'is_leading',
    ];

    protected $casts = [
        'is_leading' => 'boolean',
    ];

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }
}
