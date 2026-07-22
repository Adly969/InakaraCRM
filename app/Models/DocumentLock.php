<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $lockable_type
 * @property int $lockable_id
 * @property int $locked_by
 * @property Carbon $locked_at
 * @property Carbon $expires_at
 * @property string $reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'lockable_type',
    'lockable_id',
    'locked_by',
    'locked_at',
    'expires_at',
    'reason',
])]
class DocumentLock extends Model
{
    use HasTenantIsolation, HasUuids;

    protected $casts = [
        'locked_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user who locked the document.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }
}
