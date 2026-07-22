<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $tenant_id
 * @property string $name
 * @property string $color
 */
#[Fillable([
    'name',
    'color',
])]
class CustomerTag extends Model
{
    use HasFactory, HasTenantIsolation;

    /**
     * Get all customers assigned to this tag.
     *
     * @return BelongsToMany<Customer, $this>
     */
    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'customer_taggables', 'tag_id', 'customer_id');
    }
}
