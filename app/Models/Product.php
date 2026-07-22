<?php

namespace App\Models;

use App\Enums\AbcClassification;
use App\Enums\ProductType;
use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasTenantIsolation;
    use SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'uuid',
        'tenant_id',
        'company_id',
        'sku',
        'barcode',
        'name',
        'product_type',
        'category_id',
        'brand_id',
        'primary_uom_id',
        'safety_stock',
        'reorder_point',
        'lead_time_days',
        'abc_classification',
        'is_batch_tracked',
        'is_serial_tracked',
        'version',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'product_type' => ProductType::class,
            'abc_classification' => AbcClassification::class,
            'safety_stock' => 'decimal:4',
            'reorder_point' => 'decimal:4',
            'is_batch_tracked' => 'boolean',
            'is_serial_tracked' => 'boolean',
        ];
    }

    /** @return BelongsTo<ProductCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /** @return BelongsTo<ProductBrand, $this> */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(ProductBrand::class, 'brand_id');
    }

    /** @return BelongsTo<Unit, $this> */
    public function primaryUom(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'primary_uom_id');
    }

    /** @return HasMany<ProductVariant, $this> */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    /** @return HasMany<ProductDigitalAsset, $this> */
    public function digitalAssets(): HasMany
    {
        return $this->hasMany(ProductDigitalAsset::class, 'product_id');
    }

    /** @return HasMany<ProductAttachment, $this> */
    public function attachments(): HasMany
    {
        return $this->hasMany(ProductAttachment::class, 'product_id');
    }

    /** @return HasMany<InventoryBalance, $this> */
    public function balances(): HasMany
    {
        return $this->hasMany(InventoryBalance::class, 'product_id');
    }
}
