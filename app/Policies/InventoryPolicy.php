<?php

namespace App\Policies;

use App\Models\InventoryItem;
use App\Models\User;

class InventoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-inventory');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, InventoryItem $item): bool
    {
        return $user->hasPermissionTo('view-inventory');
    }

    /**
     * Determine whether the user can create adjustments.
     */
    public function adjust(User $user): bool
    {
        return $user->hasPermissionTo('adjust-inventory');
    }

    /**
     * Determine whether the user can approve adjustments.
     */
    public function approveAdjustment(User $user): bool
    {
        return $user->hasPermissionTo('approve-inventory-adjustment');
    }
}
