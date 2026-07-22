<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductDigitalAsset extends Model
{
    use HasTenantIsolation;

    protected $table = 'product_digital_assets';

    protected $fillable = [
        'uuid',
        'tenant_id',
        'product_id',
        'asset_type',
        'title',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'version_number',
        'uploaded_by',
    ];

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
