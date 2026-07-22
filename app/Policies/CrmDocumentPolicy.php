<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\CrmDocument;
use App\Models\User;

class CrmDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ViewDocuments->value);
    }

    public function view(User $user, CrmDocument $document): bool
    {
        if (! $user->hasPermissionTo(Permission::ViewDocuments->value)) {
            return false;
        }

        return $document->company_id === null || $document->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::UploadDocuments->value);
    }

    public function update(User $user, CrmDocument $document): bool
    {
        if (! $user->hasPermissionTo(Permission::UploadDocuments->value)) {
            return false;
        }

        if ($document->company_id !== null && $document->company_id !== $user->company_id) {
            return false;
        }

        return $document->uploaded_by === $user->id || $user->hasRole(['admin', 'owner', 'manager']);
    }

    public function delete(User $user, CrmDocument $document): bool
    {
        if (! $user->hasPermissionTo(Permission::DeleteDocuments->value)) {
            return false;
        }

        return $document->uploaded_by === $user->id || $user->hasRole(['admin', 'owner', 'manager']);
    }

    public function download(User $user, CrmDocument $document): bool
    {
        return $this->view($user, $document);
    }
}
