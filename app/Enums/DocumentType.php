<?php

namespace App\Enums;

enum DocumentType: string
{
    case Contract = 'contract';
    case FloorPlan = 'floor_plan';
    case Cad = 'cad';
    case Image = 'image';
    case Invoice = 'invoice';
    case Note = 'note';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Contract => 'Contract',
            self::FloorPlan => 'Floor Plan',
            self::Cad => 'CAD File',
            self::Image => 'Image',
            self::Invoice => 'Invoice',
            self::Note => 'Note',
            self::Other => 'Other',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Contract => 'sky',
            self::FloorPlan => 'emerald',
            self::Cad => 'amber',
            self::Image => 'rose',
            self::Invoice => 'gray',
            self::Note => 'neutral',
            self::Other => 'neutral',
        };
    }
}
