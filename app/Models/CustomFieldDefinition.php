<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $tenant_id
 * @property string $field_name
 * @property string $field_label
 * @property string $field_type
 * @property array|null $options
 * @property bool $is_required
 */
#[Fillable([
    'field_name',
    'field_label',
    'field_type',
    'options',
    'is_required',
])]
class CustomFieldDefinition extends Model
{
    use HasFactory, HasTenantIsolation;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'options' => 'json',
            'is_required' => 'boolean',
        ];
    }

    /**
     * Get all values linked to this definition.
     *
     * @return HasMany<CustomerCustomFieldValue, $this>
     */
    public function values(): HasMany
    {
        return $this->hasMany(CustomerCustomFieldValue::class, 'definition_id');
    }
}
