<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WarehouseTask;

class WarehouseTaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('execute-wms-tasks') || $user->hasPermissionTo('assign-wms-tasks') || $user->hasPermissionTo('view-inventory');
    }

    public function view(User $user, WarehouseTask $task): bool
    {
        return $user->hasPermissionTo('execute-wms-tasks') || $user->hasPermissionTo('assign-wms-tasks') || $user->hasPermissionTo('view-inventory');
    }

    public function assign(User $user): bool
    {
        return $user->hasPermissionTo('assign-wms-tasks');
    }

    public function execute(User $user, WarehouseTask $task): bool
    {
        return $user->hasPermissionTo('execute-wms-tasks');
    }
}
