<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $tenant_id
 * @property int|null $company_id
 * @property int|null $branch_id
 * @property int $customer_id
 * @property float $credit_limit
 * @property float $outstanding_receivables
 * @property float $pending_invoices
 * @property float $pending_sales_orders
 * @property bool $is_on_hold
 * @property string $risk_category
 * @property int|null $credit_score
 * @property int $version
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'customer_id',
    'credit_limit',
    'outstanding_receivables',
    'pending_invoices',
    'pending_sales_orders',
    'is_on_hold',
    'risk_category',
    'credit_score',
    'company_id',
    'branch_id',
    'version',
])]
class CreditLimit extends Model
{
    use HasTenantIsolation, HasUuids, SoftDeletes;

    protected $casts = [
        'credit_limit' => 'decimal:4',
        'outstanding_receivables' => 'decimal:4',
        'pending_invoices' => 'decimal:4',
        'pending_sales_orders' => 'decimal:4',
        'is_on_hold' => 'boolean',
    ];

    /**
     * Get the customer that owns this credit limit.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
