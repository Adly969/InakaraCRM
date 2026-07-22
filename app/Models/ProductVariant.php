<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasTenantIsolation;

    protected $table = 'product_variants';

    protected $fillable = [
        'uuid',
        'tenant_id',
        'product_id',
        'variant_sku',
        'variant_name',
        'attributes_json',
    ];

    protected function casts(): array
    {
        return [
            'attributes_json' => 'array',
        ];
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
