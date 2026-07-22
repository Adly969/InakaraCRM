<?php

namespace App\Enums;

enum WarehouseType: string
{
    case Main = 'main';
    case Transit = 'transit';
    case Quarantine = 'quarantine';
    case Bonded = 'bonded';
    case Returns = 'returns';

    public function label(): string
    {
        return match ($this) {
            self::Main => 'Main Warehouse',
            self::Transit => 'Transit Warehouse',
            self::Quarantine => 'Quarantine Facility',
            self::Bonded => 'Bonded Warehouse',
            self::Returns => 'Returns & RMA Center',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Main => 'sky',
            self::Transit => 'amber',
            self::Quarantine => 'rose',
            self::Bonded => 'purple',
            self::Returns => 'neutral',
        };
    }
}
