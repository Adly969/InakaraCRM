<?php

namespace App\Services\CRM;

use App\Models\CrmComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CommentService
{
    public function addComment(Model $commentable, string $body, User $author, ?int $parentId = null): CrmComment
    {
        return CrmComment::create([
            'commentable_type' => $commentable->getMorphClass(),
            'commentable_id' => $commentable->getKey(),
            'user_id' => $author->id,
            'body' => $body,
            'parent_id' => $parentId,
            'company_id' => $author->company_id,
        ]);
    }
}
