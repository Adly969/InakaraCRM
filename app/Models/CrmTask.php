<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmTask extends Model
{
    use HasTenantIsolation;
    use SoftDeletes;

    protected $table = 'crm_tasks';

    protected $fillable = [
        'title', 'description', 'status', 'priority', 'due_date', 'due_time',
        'completed_at', 'lead_id', 'customer_id', 'opportunity_id',
        'assigned_to', 'created_by', 'updated_by', 'parent_task_id',
        'sort_order', 'reminder_at', 'company_id', 'branch_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'reminder_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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

    /** @return HasMany<CrmTaskChecklist, $this> */
    public function checklists(): HasMany
    {
        return $this->hasMany(CrmTaskChecklist::class, 'task_id')->orderBy('sort_order');
    }

    /** @return HasMany<self, $this> */
    public function subtasks(): HasMany
    {
        return $this->hasMany(self::class, 'parent_task_id');
    }

    /** @return BelongsTo<self, $this> */
    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_task_id');
    }

    /** @return MorphMany<CrmComment, $this> */
    public function comments(): MorphMany
    {
        return $this->morphMany(CrmComment::class, 'commentable');
    }

    /** @return MorphOne<CrmReminder, $this> */
    public function reminder(): MorphOne
    {
        return $this->morphOne(CrmReminder::class, 'remindable');
    }
}
