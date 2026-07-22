<?php

namespace App\Enums;

enum CourierType: string
{
    case Internal = 'internal';
    case ThirdParty = 'third_party';
    case Expedition = 'expedition';
    case Pickup = 'pickup';

    public function label(): string
    {
        return match ($this) {
            self::Internal => 'Internal Fleet',
            self::ThirdParty => 'Third Party Courier',
            self::Expedition => 'Expedition Partner',
            self::Pickup => 'Customer Pickup',
        };
    }
}
