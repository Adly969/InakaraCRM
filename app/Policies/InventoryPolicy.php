<?php

namespace App\Policies;

use App\Models\User;

class InventoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-inventory');
    }

    public function adjust(User $user): bool
    {
        return $user->hasPermissionTo('adjust-inventory');
    }

    public function approveAdjustment(User $user): bool
    {
        return $user->hasPermissionTo('approve-inventory-adjustment');
    }

    public function transfer(User $user): bool
    {
        return $user->hasPermissionTo('transfer-inventory');
    }

    public function executeOpname(User $user): bool
    {
        return $user->hasPermissionTo('execute-stock-opname');
    }

    public function closePeriod(User $user): bool
    {
        return $user->hasPermissionTo('close-inventory-period');
    }
}
