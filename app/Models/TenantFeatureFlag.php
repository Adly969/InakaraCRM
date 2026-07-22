<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantFeatureFlag extends Model
{
    protected $table = 'tenant_feature_flags';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'feature_code',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
