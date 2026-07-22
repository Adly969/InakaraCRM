<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmActivityAttachment extends Model
{
    public $timestamps = false;

    protected $table = 'crm_activity_attachments';

    protected $fillable = [
        'activity_id', 'file_name', 'file_path', 'file_size', 'mime_type',
        'uploaded_by', 'company_id',
    ];

    /** @return BelongsTo<CrmActivity, $this> */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(CrmActivity::class, 'activity_id');
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
