<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $opportunity_id
 * @property string $competitor_name
 * @property string|null $strengths
 * @property string|null $weaknesses
 * @property Carbon $created_at
 * @property int|null $company_id
 * @property int|null $branch_id
 */
#[Fillable([
    'opportunity_id',
    'competitor_name',
    'strengths',
    'weaknesses',
    'company_id',
    'branch_id',
])]
class CrmOpportunityCompetitor extends Model
{
    use HasTenantIsolation;

    /**
     * @var string
     */
    protected $table = 'crm_opportunity_competitors';

    /**
     * Disable updated_at since competitors are write-once per opportunity.
     */
    public const UPDATED_AT = null;

    /**
     * Get the opportunity.
     *
     * @return BelongsTo<Opportunity, $this>
     */
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }
}
