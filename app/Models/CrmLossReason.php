<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $company_id
 * @property int|null $branch_id
 */
#[Fillable([
    'name',
    'is_active',
    'company_id',
    'branch_id',
])]
class CrmLossReason extends Model
{
    use HasTenantIsolation;

    /**
     * @var string
     */
    protected $table = 'crm_loss_reasons';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
