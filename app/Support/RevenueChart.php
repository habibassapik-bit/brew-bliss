<?php

namespace App\Support;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class RevenueChart
{
    /**
     * Susun titik grafik omzet per bucket waktu dalam periode.
     * Bucket hari untuk periode pendek, bulan untuk periode tahunan;
     * bucket tanpa transaksi bernilai nol. Definisi omzet sama dengan
     * Dashboard Step 6: paid, bukan cancelled, dihitung dari created_at.
     *
     * @return array{points: list<array{label: string, value: float, x: float, y: float}>, max: float, hasData: bool}
     */
    public static function build(string $period, Carbon $start, Carbon $end): array
    {
        $daily = $period !== 'year';

        $buckets = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $key = $daily ? $cursor->toDateString() : $cursor->format('Y-m');
            $buckets[$key] = 0.0;
            $cursor->add(1, $daily ? 'day' : 'month');
        }

        DB::table('orders')
            ->whereBetween('created_at', [$start, $end])
            ->where('payment_status', PaymentStatus::Paid->value)
            ->where('status', '!=', OrderStatus::Cancelled->value)
            ->selectRaw(
                $daily
                    ? 'DATE(created_at) as bucket, SUM(total) as revenue'
                    : self::monthlyBucketExpression().' as bucket, SUM(total) as revenue'
            )
            ->groupBy('bucket')
            ->pluck('revenue', 'bucket')
            ->each(function (float $revenue, string $key) use (&$buckets): void {
                if (array_key_exists($key, $buckets)) {
                    $buckets[$key] = (float) $revenue;
                }
            });

        $max = max($buckets);
        $hasData = $max > 0.0;

        $count = count($buckets);
        $index = 0;
        $points = [];
        foreach ($buckets as $label => $value) {
            $x = $count > 1 ? 100 * $index / ($count - 1) : 100.0;
            $y = $hasData && $value > 0.0 ? 100 - 100 * $value / $max : 100.0;
            $points[] = ['label' => $label, 'value' => $value, 'x' => round($x, 2), 'y' => round($y, 2)];
            $index++;
        }

        return ['points' => $points, 'max' => (float) $max, 'hasData' => $hasData];
    }

    /**
     * Ekspresi bucket bulanan sesuai driver database aktif.
     */
    private static function monthlyBucketExpression(): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";
    }
}
