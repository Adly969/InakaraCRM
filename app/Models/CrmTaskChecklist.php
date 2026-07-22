<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmTaskChecklist extends Model
{
    protected $table = 'crm_task_checklists';

    protected $fillable = [
        'task_id',
        'label',
        'is_completed',
        'completed_at',
        'completed_by',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<CrmTask, $this> */
    public function task(): BelongsTo
    {
        return $this->belongsTo(CrmTask::class, 'task_id');
    }

    /** @return BelongsTo<User, $this> */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
