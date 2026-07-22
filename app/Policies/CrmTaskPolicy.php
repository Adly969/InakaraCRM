<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\CrmTask;
use App\Models\User;

class CrmTaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ViewTasks->value);
    }

    public function view(User $user, CrmTask $task): bool
    {
        if (! $user->hasPermissionTo(Permission::ViewTasks->value)) {
            return false;
        }

        return $task->company_id === null || $task->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::CreateTasks->value);
    }

    public function update(User $user, CrmTask $task): bool
    {
        if (! $user->hasPermissionTo(Permission::EditTasks->value)) {
            return false;
        }

        if ($task->company_id !== null && $task->company_id !== $user->company_id) {
            return false;
        }

        return $task->created_by === $user->id || $task->assigned_to === $user->id || $user->hasRole(['admin', 'owner', 'manager']);
    }

    public function delete(User $user, CrmTask $task): bool
    {
        if (! $user->hasPermissionTo(Permission::DeleteTasks->value)) {
            return false;
        }

        return $task->company_id === null || $task->company_id === $user->company_id;
    }
}
