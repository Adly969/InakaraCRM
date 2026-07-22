<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $commentable_type
 * @property int $commentable_id
 * @property int $user_id
 * @property string $body
 * @property string|null $parent_id
 * @property bool $is_pinned
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'commentable_type',
    'commentable_id',
    'user_id',
    'body',
    'parent_id',
    'is_pinned',
])]
class Comment extends Model
{
    use HasTenantIsolation, HasUuids;

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    /**
     * Get the owning commentable model.
     *
     * @return MorphTo<Model, $this>
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who left the comment.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment if thread-reply.
     *
     * @return BelongsTo<Comment, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get all child replies to this comment.
     *
     * @return HasMany<Comment, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
}
