<?php

namespace App\Policies;

use App\Enums\GoodsIssueStatus;
use App\Models\GoodsIssue;
use App\Models\User;

class GoodsIssuePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-goods-issues');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GoodsIssue $gi): bool
    {
        return $user->hasPermissionTo('view-goods-issues');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-goods-issues');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GoodsIssue $gi): bool
    {
        return $user->hasPermissionTo('create-goods-issues') && $gi->status === GoodsIssueStatus::Draft;
    }

    /**
     * Determine whether the user can approve/issue the model.
     */
    public function approve(User $user, GoodsIssue $gi): bool
    {
        return $user->hasPermissionTo('approve-goods-issues') && $gi->status === GoodsIssueStatus::Draft;
    }
}
