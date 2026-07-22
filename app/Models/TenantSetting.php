<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $tenant_id
 * @property string $default_currency
 * @property int $decimal_precision
 * @property string $invoice_prefix
 * @property string $so_prefix
 * @property string $credit_limit_policy
 * @property int $reminder_days
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'tenant_id',
    'default_currency',
    'decimal_precision',
    'invoice_prefix',
    'so_prefix',
    'credit_limit_policy',
    'reminder_days',
])]
class TenantSetting extends Model
{
    protected $primaryKey = 'tenant_id';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * Get the tenant linked to these settings.
     *
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
