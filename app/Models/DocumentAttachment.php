<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $attachable_type
 * @property int $attachable_id
 * @property string $disk
 * @property string $path
 * @property string $filename
 * @property string $mime_type
 * @property int $size
 * @property int $version
 * @property int|null $uploaded_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'attachable_type',
    'attachable_id',
    'disk',
    'path',
    'filename',
    'mime_type',
    'size',
    'version',
    'uploaded_by',
])]
class DocumentAttachment extends Model
{
    use HasTenantIsolation, HasUuids;

    /**
     * Get the owning model.
     *
     * @return MorphTo<Model, $this>
     */
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
