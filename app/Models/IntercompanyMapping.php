<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntercompanyMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_company_id',
        'destination_company_id',
        'due_from_account_id',
        'due_to_account_id',
    ];

    public function sourceCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'source_company_id');
    }

    public function destinationCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'destination_company_id');
    }

    public function dueFromAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'due_from_account_id');
    }

    public function dueToAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'due_to_account_id');
    }
}
