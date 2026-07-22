<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $document_type
 * @property int $document_id
 * @property string $customer_name
 * @property array $billing_address
 * @property array $shipping_address
 * @property string|null $tax_id
 * @property string $currency
 * @property string|null $payment_terms
 * @property string|null $agent_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'document_type',
    'document_id',
    'customer_name',
    'billing_address',
    'shipping_address',
    'tax_id',
    'currency',
    'payment_terms',
    'agent_name',
])]
class DocumentSnapshot extends Model
{
    use HasTenantIsolation, HasUuids;

    protected $casts = [
        'billing_address' => 'array',
        'shipping_address' => 'array',
    ];
}
