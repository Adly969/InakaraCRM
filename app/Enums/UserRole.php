<?php

namespace App\Enums;

enum UserRole: string
{
    case Owner = 'owner';
    case Manager = 'manager';
    case Sales = 'sales';
    case Admin = 'admin';
    case Finance = 'finance';
    case Gudang = 'gudang';
    case Produksi = 'produksi';
    case CustomerService = 'customer-service';
    case Viewer = 'viewer';

    /**
     * Get the human-readable display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Manager => 'Manager',
            self::Sales => 'Sales',
            self::Admin => 'Admin',
            self::Finance => 'Finance',
            self::Gudang => 'Gudang',
            self::Produksi => 'Produksi',
            self::CustomerService => 'Customer Service',
            self::Viewer => 'Viewer',
        };
    }
}
