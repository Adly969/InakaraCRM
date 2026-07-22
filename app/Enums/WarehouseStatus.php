<?php

namespace App\Enums;

enum WarehouseStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    /**
     * Get the human-readable display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
        };
    }
}
