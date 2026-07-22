<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostingRule extends Model
{
    use HasFactory, HasTenantIsolation;

    protected $fillable = [
        'company_id',
        'branch_id',
        'event_type',
        'transaction_attribute',
        'debit_account_id',
        'credit_account_id',
        'description',
    ];

    public function debitAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'debit_account_id');
    }

    public function creditAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'credit_account_id');
    }
}
