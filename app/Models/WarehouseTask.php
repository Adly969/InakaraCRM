<?php

namespace App\Models;

use App\Enums\WarehouseTaskStatus;
use App\Enums\WarehouseTaskType;
use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseTask extends Model
{
    use HasTenantIsolation;
    use SoftDeletes;

    protected $table = 'warehouse_tasks';

    protected $fillable = [
        'uuid',
        'tenant_id',
        'company_id',
        'task_number',
        'task_type',
        'status',
        'priority',
        'warehouse_id',
        'assigned_operator_id',
        'due_date',
        'estimated_duration_minutes',
        'actual_duration_minutes',
        'started_at',
        'completed_at',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'task_type' => WarehouseTaskType::class,
            'status' => WarehouseTaskStatus::class,
            'due_date' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Warehouse, $this> */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /** @return BelongsTo<User, $this> */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_operator_id');
    }

    /** @return HasMany<WarehouseTaskItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(WarehouseTaskItem::class, 'task_id');
    }
}
