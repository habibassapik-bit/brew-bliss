<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PendingConfirmation = 'pending_confirmation';

    case AwaitingPayment = 'awaiting_payment';

    case Processing = 'processing';

    case ReadyPickup = 'ready_pickup';

    case Shipping = 'shipping';

    case Completed = 'completed';

    case Cancelled = 'cancelled';

    /**
     * Daftar nilai status untuk validasi.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::PendingConfirmation => 'Menunggu Konfirmasi',
            self::AwaitingPayment => 'Menunggu Pembayaran',
            self::Processing => 'Diproses',
            self::ReadyPickup => 'Siap Diambil',
            self::Shipping => 'Dalam Pengiriman',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }
}
