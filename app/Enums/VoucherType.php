<?php

namespace App\Enums;

enum VoucherType: string
{
    case Fixed = 'fixed';

    case Percent = 'percent';

    public function label(): string
    {
        return match ($this) {
            self::Fixed => 'Nominal (Rp)',
            self::Percent => 'Persentase (%)',
        };
    }
}
