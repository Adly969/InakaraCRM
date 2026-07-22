<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\CrmActivity;
use App\Models\User;

class ActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ViewActivities->value);
    }

    public function view(User $user, CrmActivity $activity): bool
    {
        if (! $user->hasPermissionTo(Permission::ViewActivities->value)) {
            return false;
        }

        return $activity->company_id === null || $activity->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::CreateActivities->value);
    }

    public function update(User $user, CrmActivity $activity): bool
    {
        if (! $user->hasPermissionTo(Permission::EditActivities->value)) {
            return false;
        }

        if ($activity->company_id !== null && $activity->company_id !== $user->company_id) {
            return false;
        }

        return $activity->created_by === $user->id || $activity->assigned_to === $user->id || $user->hasRole(['admin', 'owner', 'manager']);
    }

    public function delete(User $user, CrmActivity $activity): bool
    {
        if (! $user->hasPermissionTo(Permission::DeleteActivities->value)) {
            return false;
        }

        return $activity->company_id === null || $activity->company_id === $user->company_id;
    }
}
