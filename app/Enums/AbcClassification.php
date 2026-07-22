<?php

namespace App\Enums;

enum AbcClassification: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';

    public function label(): string
    {
        return match ($this) {
            self::A => 'Class A (High Value / High Velocity)',
            self::B => 'Class B (Moderate Value)',
            self::C => 'Class C (Low Value / High Volume)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::A => 'rose',
            self::B => 'amber',
            self::C => 'emerald',
        };
    }
}
