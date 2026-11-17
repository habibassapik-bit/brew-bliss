<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';

    case PendingVerification = 'pending_verification';

    case Paid = 'paid';

    case Rejected = 'rejected';

    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Belum Dibayar',
            self::PendingVerification => 'Menunggu Verifikasi',
            self::Paid => 'Dibayar',
            self::Rejected => 'Ditolak',
            self::Refunded => 'Dikembalikan',
        };
    }
}
