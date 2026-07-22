<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property int $branch_id
 * @property int $customer_id
 * @property string $contract_number
 * @property string $status
 * @property string $start_date
 * @property string $end_date
 * @property string|null $terms_conditions
 * @property float|null $credit_limit_override
 * @property int $version
 */
#[Fillable([
    'company_id',
    'branch_id',
    'customer_id',
    'contract_number',
    'status',
    'start_date',
    'end_date',
    'terms_conditions',
    'credit_limit_override',
    'version',
])]
class CustomerContract extends Model
{
    use HasFactory, HasTenantIsolation;

    /**
     * Get the customer associated with the contract.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
