<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BudgetEntry extends Model
{
    use HasFactory, HasTenantIsolation;

    protected $fillable = [
        'company_id',
        'branch_id',
        'account_id',
        'fiscal_year',
        'budget_amount',
        'revision_number',
    ];

    protected $casts = [
        'budget_amount' => 'decimal:2',
        'revision_number' => 'integer',
        'fiscal_year' => 'integer',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function dimensionValues(): BelongsToMany
    {
        return $this->belongsToMany(
            FinancialDimensionValue::class,
            'budget_dimensions',
            'budget_entry_id',
            'financial_dimension_value_id'
        )->withTimestamps();
    }
}
