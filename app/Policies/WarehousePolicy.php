<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Warehouse;

class WarehousePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-warehouses');
    }

    public function view(User $user, Warehouse $warehouse): bool
    {
        return $user->hasPermissionTo('view-warehouses');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-warehouses');
    }

    public function update(User $user, Warehouse $warehouse): bool
    {
        return $user->hasPermissionTo('edit-warehouses');
    }

    public function delete(User $user, Warehouse $warehouse): bool
    {
        return $user->hasPermissionTo('delete-warehouses');
    }
}
