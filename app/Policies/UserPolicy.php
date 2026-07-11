<?php

namespace App\Policies;

use App\Enums\Permission as AppPermission;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(AppPermission::ViewUsers->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasPermissionTo(AppPermission::ViewUsers->value) || $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(AppPermission::CreateUsers->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        if (! $user->hasPermissionTo(AppPermission::EditUsers->value)) {
            return false;
        }

        // Cannot edit Owner unless the acting user is also an Owner
        if ($model->hasRole(\App\Enums\UserRole::Owner->value) && ! $user->hasRole(\App\Enums\UserRole::Owner->value)) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        if (! $user->hasPermissionTo(AppPermission::DeleteUsers->value)) {
            return false;
        }

        // Cannot delete self
        if ($user->id === $model->id) {
            return false;
        }

        // Cannot delete Owner
        if ($model->hasRole(\App\Enums\UserRole::Owner->value)) {
            return false;
        }

        return true;
    }
}
