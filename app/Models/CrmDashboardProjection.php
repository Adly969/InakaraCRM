<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $tenant_id
 * @property string $metric_key
 * @property float $metric_value
 * @property string $last_updated_at
 */
#[Fillable([
    'tenant_id',
    'metric_key',
    'metric_value',
    'last_updated_at',
])]
class CrmDashboardProjection extends Model
{
    use HasFactory;

    /**
     * Define custom table name.
     */
    protected $table = 'crm_dashboard_projections';

    /**
     * Disable model timestamps.
     */
    public $timestamps = false;

    /**
     * Disable auto-incrementing key since we use compound PK.
     */
    public $incrementing = false;

    /**
     * Define custom primary keys.
     */
    protected $primaryKey = ['tenant_id', 'metric_key'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metric_value' => 'float',
        ];
    }
}
