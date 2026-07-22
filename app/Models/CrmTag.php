<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $color_hex
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $company_id
 * @property int|null $branch_id
 */
#[Fillable([
    'name',
    'color_hex',
    'company_id',
    'branch_id',
])]
class CrmTag extends Model
{
    use HasTenantIsolation;

    /**
     * @var string
     */
    protected $table = 'crm_tags';
}
