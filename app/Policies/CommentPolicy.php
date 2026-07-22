<?php

namespace App\Policies;

use App\Models\CrmComment;
use App\Models\User;

class CommentPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, CrmComment $comment): bool
    {
        return $comment->user_id === $user->id || $user->hasRole(['admin', 'owner']);
    }

    public function delete(User $user, CrmComment $comment): bool
    {
        return $comment->user_id === $user->id || $user->hasRole(['admin', 'owner', 'manager']);
    }
}
