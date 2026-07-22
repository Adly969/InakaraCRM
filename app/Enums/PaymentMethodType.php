<?php

namespace App\Enums;

enum PaymentMethodType: string
{
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case Cheque = 'cheque';
    case Giro = 'giro';
    case VirtualAccount = 'virtual_account';
    case Qris = 'qris';
    case CreditCard = 'credit_card';
    case Other = 'other';

    /**
     * Get the human-readable display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::BankTransfer => 'Bank Transfer',
            self::Cheque => 'Cheque',
            self::Giro => 'Giro',
            self::VirtualAccount => 'Virtual Account',
            self::Qris => 'QRIS',
            self::CreditCard => 'Credit Card',
            self::Other => 'Other',
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
