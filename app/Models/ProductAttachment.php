<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttachment extends Model
{
    use HasTenantIsolation;

    protected $table = 'product_attachments';

    protected $fillable = [
        'uuid',
        'tenant_id',
        'product_id',
        'category',
        'title',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
    ];

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
