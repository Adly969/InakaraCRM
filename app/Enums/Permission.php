<?php

namespace App\Enums;

enum Permission: string
{
    case ViewDashboard = 'view-dashboard';
    case ViewUsers = 'view-users';
    case CreateUsers = 'create-users';
    case EditUsers = 'edit-users';
    case DeleteUsers = 'delete-users';
    case ViewSettings = 'view-settings';
    case ManageSettings = 'manage-settings';

    /**
     * Get the human-readable display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::ViewDashboard => 'View Dashboard',
            self::ViewUsers => 'View Users',
            self::CreateUsers => 'Create Users',
            self::EditUsers => 'Edit Users',
            self::DeleteUsers => 'Delete Users',
            self::ViewSettings => 'View Settings',
            self::ManageSettings => 'Manage Settings',
        };
    }
}
