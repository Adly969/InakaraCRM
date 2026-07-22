<?php

namespace App\Models;

use App\Enums\HeatScore;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Traits\HasTenantIsolation;
use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $reference_no
 * @property string $name
 * @property string|null $company_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $website
 * @property string|null $job_title
 * @property LeadSource $source
 * @property string|null $campaign_source
 * @property LeadStatus $status
 * @property string $priority
 * @property HeatScore $heat_score
 * @property string|null $disqualification_reason
 * @property int|null $assigned_to
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $company_id
 * @property int|null $branch_id
 * @property int $version
 */
#[Fillable([
    'reference_no',
    'name',
    'company_name',
    'email',
    'phone',
    'website',
    'job_title',
    'source',
    'campaign_source',
    'status',
    'priority',
    'heat_score',
    'disqualification_reason',
    'assigned_to',
    'created_by',
    'updated_by',
    'deleted_by',
    'company_id',
    'branch_id',
    'city',
    'province',
    'postal_code',
    'version',
])]
class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory, HasTenantIsolation, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'source' => LeadSource::class,
            'status' => LeadStatus::class,
            'heat_score' => HeatScore::class,
        ];
    }

    /**
     * Get the user assigned to this lead.
     *
     * @return BelongsTo<User, $this>
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created this lead.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this lead.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this lead.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get all CRM activities for this lead.
     *
     * @return HasMany<Activity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Get opportunities converted from this lead.
     *
     * @return HasMany<Opportunity, $this>
     */
    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }
}
