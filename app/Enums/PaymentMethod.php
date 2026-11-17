<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Qris = 'qris';

    case BankTransfer = 'bank_transfer';

    case Ewallet = 'ewallet';

    case Cod = 'cod';

    public function label(): string
    {
        return match ($this) {
            self::Qris => 'QRIS',
            self::BankTransfer => 'Transfer Bank',
            self::Ewallet => 'E-Wallet',
            self::Cod => 'COD (Bayar di Tempat)',
        };
    }

    public function requiresProof(): bool
    {
        return in_array($this, [self::Qris, self::BankTransfer, self::Ewallet], true);
    }
}
