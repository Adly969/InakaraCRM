<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmDocumentVersion extends Model
{
    public $timestamps = false;

    protected $table = 'crm_document_versions';

    protected $fillable = [
        'document_id',
        'version_number',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'checksum',
        'uploaded_by',
        'change_notes',
    ];

    /** @return BelongsTo<CrmDocument, $this> */
    public function document(): BelongsTo
    {
        return $this->belongsTo(CrmDocument::class, 'document_id');
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
