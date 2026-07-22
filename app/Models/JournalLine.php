<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JournalLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'debit_amount',
        'credit_amount',
        'currency_code',
        'exchange_rate',
        'base_debit_amount',
        'base_credit_amount',
    ];

    protected $casts = [
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'base_debit_amount' => 'decimal:2',
        'base_credit_amount' => 'decimal:2',
    ];

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function dimensionValues(): BelongsToMany
    {
        return $this->belongsToMany(
            FinancialDimensionValue::class,
            'journal_line_dimensions',
            'journal_line_id',
            'financial_dimension_value_id'
        )->withTimestamps();
    }
}
