<?php

namespace App\Enums;

enum FeedbackStatus: string
{
    case New = 'new';

    case Read = 'read';

    case InProgress = 'in_progress';

    case Done = 'done';

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
            self::New => 'Baru',
            self::Read => 'Dibaca',
            self::InProgress => 'Ditindaklanjuti',
            self::Done => 'Selesai',
        };
    }
}
