<?php

namespace App\Enums;

enum CrmActivityType: string
{
    case PhoneCall = 'phone_call';
    case Email = 'email';
    case Meeting = 'meeting';
    case SiteVisit = 'site_visit';
    case Note = 'note';
    case WhatsApp = 'whatsapp';

    /**
     * Get the human-readable display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::PhoneCall => 'Phone Call',
            self::Email => 'Email',
            self::Meeting => 'Meeting',
            self::SiteVisit => 'Site Visit',
            self::Note => 'Note',
            self::WhatsApp => 'WhatsApp',
        };
    }

    /**
     * Get the icon name for UI rendering.
     */
    public function icon(): string
    {
        return match ($this) {
            self::PhoneCall => 'phone',
            self::Email => 'mail',
            self::Meeting => 'calendar',
            self::SiteVisit => 'map-pin',
            self::Note => 'file-text',
            self::WhatsApp => 'message-circle',
        };
    }
}
