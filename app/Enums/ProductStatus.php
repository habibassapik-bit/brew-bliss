<?php

namespace App\Enums;

enum ProductStatus: string
{
    case Available = 'available';

    case SoldOut = 'sold_out';

    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Tersedia',
            self::SoldOut => 'Habis',
            self::Inactive => 'Nonaktif',
        };
    }
}
