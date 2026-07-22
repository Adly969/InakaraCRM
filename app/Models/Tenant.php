<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use SoftDeletes;

    protected $table = 'tenants';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'slug',
        'status',
        'version',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'tenant_id');
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'tenant_id');
    }

    public function featureFlags(): HasMany
    {
        return $this->hasMany(TenantFeatureFlag::class, 'tenant_id');
    }

    public function featureEnabled(string $featureCode): bool
    {
        $flags = cache()->remember("tenant:{$this->id}:flags", 60, function () {
            return $this->featureFlags()->pluck('is_enabled', 'feature_code')->toArray();
        });

        return (bool) ($flags[$featureCode] ?? false);
    }
}
