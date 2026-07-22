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
 * @property string $agreement_number
 * @property float $commitment_amount
 * @property float $consumed_amount
 * @property string $start_date
 * @property string $end_date
 * @property int $version
 */
#[Fillable([
    'company_id',
    'branch_id',
    'customer_id',
    'agreement_number',
    'commitment_amount',
    'consumed_amount',
    'start_date',
    'end_date',
    'version',
])]
class SalesAgreement extends Model
{
    use HasFactory, HasTenantIsolation;

    /**
     * Get the customer associated with the agreement.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
