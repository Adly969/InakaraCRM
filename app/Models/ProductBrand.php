<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Model;

class ProductBrand extends Model
{
    use HasTenantIsolation;

    protected $table = 'product_brands';

    protected $fillable = [
        'uuid',
        'tenant_id',
        'name',
    ];
}
