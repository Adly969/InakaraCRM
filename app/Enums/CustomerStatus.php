<?php

namespace App\Enums;

enum CustomerStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Blacklisted = 'blacklisted';
    case Suspended = 'suspended';
    case Archived = 'archived';
    case Merged = 'merged';

    /**
     * Get the human-readable display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Blacklisted => 'Blacklisted',
            self::Suspended => 'Suspended',
            self::Archived => 'Archived',
            self::Merged => 'Merged',
        };
    }
}
