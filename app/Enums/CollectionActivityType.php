<?php

namespace App\Enums;

enum CollectionActivityType: string
{
    case PhoneCall = 'phone_call';
    case WhatsApp = 'whatsapp';
    case Email = 'email';
    case Visit = 'visit';
    case PromiseToPay = 'promise_to_pay';
    case BrokenPromise = 'broken_promise';

    /**
     * Get the display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::PhoneCall => 'Phone Call',
            self::WhatsApp => 'WhatsApp',
            self::Email => 'Email',
            self::Visit => 'Visit',
            self::PromiseToPay => 'Promise To Pay',
            self::BrokenPromise => 'Broken Promise',
        };
    }

    /**
     * Get all values.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
