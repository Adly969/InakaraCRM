<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $customer_id
 * @property int $definition_id
 * @property string|null $value
 */
#[Fillable([
    'customer_id',
    'definition_id',
    'value',
])]
class CustomerCustomFieldValue extends Model
{
    use HasFactory;

    /**
     * Disable model timestamps for simple key-value table.
     */
    public $timestamps = false;

    /**
     * Disable auto-incrementing key since we use compound PK.
     */
    public $incrementing = false;

    /**
     * Define custom primary keys.
     */
    protected $primaryKey = ['customer_id', 'definition_id'];

    /**
     * Get the customer associated with the custom value.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the definition metadata.
     *
     * @return BelongsTo<CustomFieldDefinition, $this>
     */
    public function definition(): BelongsTo
    {
        return $this->belongsTo(CustomFieldDefinition::class, 'definition_id');
    }
}
