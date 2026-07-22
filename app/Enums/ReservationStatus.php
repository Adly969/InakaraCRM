<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Released = 'released';
    case Consumed = 'consumed';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Expired => 'Expired',
            self::Released => 'Released',
            self::Consumed => 'Consumed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'emerald',
            self::Expired => 'rose',
            self::Released => 'gray',
            self::Consumed => 'sky',
        };
    }
}
