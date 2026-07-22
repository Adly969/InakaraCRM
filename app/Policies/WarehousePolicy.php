<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Warehouse;

class WarehousePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-warehouses');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Warehouse $warehouse): bool
    {
        return $user->hasPermissionTo('view-warehouses');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['owner', 'admin', 'manager']) && $user->hasPermissionTo('create-warehouses');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Warehouse $warehouse): bool
    {
        return $user->hasAnyRole(['owner', 'admin', 'manager']) && $user->hasPermissionTo('edit-warehouses');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Warehouse $warehouse): bool
    {
        return $user->hasAnyRole(['owner', 'admin']) && $user->hasPermissionTo('delete-warehouses');
    }
}
