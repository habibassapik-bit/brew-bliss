<?php

namespace App\Support;

use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

final class ReportPeriod
{
    /**
     * Daftar opsi filter periode yang tersedia.
     */
    public const PERIODS = ['today', 'last7', 'month', 'year', 'custom'];

    /**
     * Tentukan rentang periode dari input request dengan fallback aman.
     * Definisi omzet mengikuti Dashboard Step 6: dibuat pada periode,
     * payment_status paid, status bukan cancelled.
     *
     * @return array{period: string, start: Carbon, end: Carbon, from: ?string, to: ?string}
     */
    public static function fromRequest(Request $request): array
    {
        $period = $request->string('period');

        $period = in_array($period->value, self::PERIODS, true) ? $period->value : 'today';

        if ($period === 'custom') {
            try {
                $from = $request->date('from', 'Y-m-d');
                $to = $request->date('to', 'Y-m-d');
            } catch (InvalidFormatException) {
                $from = null;
                $to = null;
            }

            if ($from !== null && $to !== null && $from->lte($to)) {
                return [
                    'custom',
                    $from->startOfDay(),
                    $to->endOfDay(),
                    $from->toDateString(),
                    $to->toDateString(),
                ];
            }

            $period = 'today';
        }

        return [
            $period,
            match ($period) {
                'last7' => Carbon::today()->subDays(6)->startOfDay(),
                'month' => Carbon::today()->startOfMonth(),
                'year' => Carbon::today()->startOfYear(),
                default => Carbon::today()->startOfDay(),
            },
            Carbon::today()->endOfDay(),
            null,
            null,
        ];
    }

    /**
     * Label manusiawi untuk periode yang dipilih.
     */
    public static function label(string $period, ?string $from = null, ?string $to = null): string
    {
        return match ($period) {
            'last7' => '7 hari terakhir',
            'month' => 'Bulan ini',
            'year' => 'Tahun ini',
            'custom' => 'Custom: '.($from ?? '?').' s.d. '.($to ?? '?'),
            default => 'Hari ini',
        };
    }
}
