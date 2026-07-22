<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $tenant_id
 * @property int $activity_id
 * @property string $disk
 * @property string $path
 * @property string $filename
 * @property string $mime_type
 * @property int $size
 * @property int|null $uploaded_by
 * @property string $created_at
 */
#[Fillable([
    'activity_id',
    'disk',
    'path',
    'filename',
    'mime_type',
    'size',
    'uploaded_by',
])]
class ActivityAttachment extends Model
{
    use HasFactory, HasTenantIsolation;

    /**
     * Disable updated_at since uploads are typically immutable.
     */
    const UPDATED_AT = null;

    /**
     * Get the activity timeline log card.
     *
     * @return BelongsTo<Activity, $this>
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * Get the user who executed the upload.
     *
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
