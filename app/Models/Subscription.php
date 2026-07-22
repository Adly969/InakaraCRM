<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Subscription extends Model
{
    use SoftDeletes;

    protected $table = 'subscriptions';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tenant_id',
        'plan_name',
        'status',
        'starts_at',
        'ends_at',
        'grace_ends_at',
        'version',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'grace_ends_at' => 'datetime',
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

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function isActive(): bool
    {
        if ($this->status === 'suspended') {
            return false;
        }

        $now = now();
        if ($this->ends_at && $now->greaterThan($this->ends_at)) {
            if ($this->grace_ends_at && $now->lessThanOrEqualTo($this->grace_ends_at)) {
                return true;
            }

            return false;
        }

        return in_array($this->status, ['active', 'trial']);
    }
}
