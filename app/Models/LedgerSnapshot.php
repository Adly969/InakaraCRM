<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerSnapshot extends Model
{
    use HasFactory, HasTenantIsolation;

    protected $fillable = [
        'company_id',
        'branch_id',
        'ledger_id',
        'account_id',
        'fiscal_year',
        'fiscal_month',
        'opening_balance',
        'total_debits',
        'total_credits',
        'closing_balance',
        'is_frozen',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'total_debits' => 'decimal:2',
        'total_credits' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'is_frozen' => 'boolean',
        'fiscal_year' => 'integer',
        'fiscal_month' => 'integer',
    ];

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
