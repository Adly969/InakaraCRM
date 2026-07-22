<?php

namespace App\Enums;

enum WarehouseStatus: string
{
    case Active = 'active';
    case Maintenance = 'maintenance';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Maintenance => 'Under Maintenance',
            self::Inactive => 'Inactive',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'emerald',
            self::Maintenance => 'amber',
            self::Inactive => 'gray',
        };
    }
}
