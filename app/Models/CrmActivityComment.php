<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmActivityComment extends Model
{
    use HasTenantIsolation;
    use SoftDeletes;

    protected $table = 'crm_activity_comments';

    protected $fillable = [
        'activity_id', 'user_id', 'body', 'parent_id', 'company_id', 'branch_id',
    ];

    /** @return BelongsTo<CrmActivity, $this> */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(CrmActivity::class, 'activity_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<self, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
