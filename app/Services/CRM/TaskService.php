<?php

namespace App\Services\CRM;

use App\Enums\TaskStatus;
use App\Models\CrmTask;
use App\Models\CrmTaskChecklist;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaskService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = CrmTask::query()
            ->with(['assignedTo:id,name', 'creator:id,name', 'customer:id,name', 'lead:id,first_name,last_name,company_name', 'opportunity:id,title', 'checklists'])
            ->orderBy('due_date', 'asc');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (! empty($filters['lead_id'])) {
            $query->where('lead_id', $filters['lead_id']);
        }

        if (! empty($filters['opportunity_id'])) {
            $query->where('opportunity_id', $filters['opportunity_id']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $creator): CrmTask
    {
        return DB::transaction(function () use ($data, $creator) {
            $task = CrmTask::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? TaskStatus::Pending->value,
                'priority' => $data['priority'] ?? 'medium',
                'due_date' => $data['due_date'],
                'due_time' => $data['due_time'] ?? null,
                'lead_id' => $data['lead_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'opportunity_id' => $data['opportunity_id'] ?? null,
                'assigned_to' => $data['assigned_to'] ?? $creator->id,
                'created_by' => $creator->id,
                'parent_task_id' => $data['parent_task_id'] ?? null,
                'reminder_at' => $data['reminder_at'] ?? null,
                'company_id' => $creator->company_id,
                'branch_id' => $creator->branch_id,
            ]);

            if (! empty($data['checklists']) && is_array($data['checklists'])) {
                foreach ($data['checklists'] as $index => $label) {
                    if (is_string($label) && trim($label) !== '') {
                        $task->checklists()->create([
                            'label' => trim($label),
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            return $task->fresh(['checklists']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(CrmTask $task, array $data, User $updater): CrmTask
    {
        if (isset($data['version']) && (int) $data['version'] !== $task->version) {
            throw ValidationException::withMessages([
                'version' => ['This task has been modified by another user. Please refresh.'],
            ]);
        }

        return DB::transaction(function () use ($task, $data, $updater) {
            $task->update(array_merge($data, [
                'updated_by' => $updater->id,
                'version' => $task->version + 1,
            ]));

            return $task->fresh(['checklists']);
        });
    }

    public function toggleStatus(CrmTask $task, TaskStatus $newStatus, User $user): CrmTask
    {
        return DB::transaction(function () use ($task, $newStatus, $user) {
            $task->update([
                'status' => $newStatus,
                'completed_at' => $newStatus === TaskStatus::Completed ? now() : null,
                'updated_by' => $user->id,
                'version' => $task->version + 1,
            ]);

            return $task->fresh();
        });
    }

    public function toggleChecklist(CrmTaskChecklist $checklist, User $user): CrmTaskChecklist
    {
        $newCompleted = ! $checklist->is_completed;
        $checklist->update([
            'is_completed' => $newCompleted,
            'completed_at' => $newCompleted ? now() : null,
            'completed_by' => $newCompleted ? $user->id : null,
        ]);

        return $checklist->fresh();
    }
}
