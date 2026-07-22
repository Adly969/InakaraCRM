<?php

namespace App\Models;

use App\Enums\DocumentType;
use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmDocument extends Model
{
    use HasTenantIsolation;
    use SoftDeletes;

    protected $table = 'crm_documents';

    protected $fillable = [
        'title',
        'description',
        'document_type',
        'folder_id',
        'lead_id',
        'customer_id',
        'opportunity_id',
        'quotation_id',
        'is_pinned',
        'uploaded_by',
        'company_id',
        'branch_id',
    ];

    protected function casts(): array
    {
        return [
            'document_type' => DocumentType::class,
            'is_pinned' => 'boolean',
        ];
    }

    /** @return BelongsTo<CrmDocumentFolder, $this> */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(CrmDocumentFolder::class, 'folder_id');
    }

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return BelongsTo<Opportunity, $this> */
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    /** @return BelongsTo<Quotation, $this> */
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** @return HasMany<CrmDocumentVersion, $this> */
    public function versions(): HasMany
    {
        return $this->hasMany(CrmDocumentVersion::class, 'document_id')->orderBy('version_number', 'desc');
    }

    /** @return HasOne<CrmDocumentVersion, $this> */
    public function latestVersion(): HasOne
    {
        return $this->hasOne(CrmDocumentVersion::class, 'document_id')->latestOfMany('version_number');
    }

    /** @return MorphMany<CrmComment, $this> */
    public function comments(): MorphMany
    {
        return $this->morphMany(CrmComment::class, 'commentable');
    }
}
