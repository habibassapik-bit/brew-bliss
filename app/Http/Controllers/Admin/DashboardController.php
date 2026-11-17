<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Daftar opsi filter periode yang tersedia.
     */
    private const PERIODS = ['today', 'last7', 'month', 'year', 'custom'];

    /**
     * Tampilkan dashboard Super Admin dengan statistik nyata sesuai periode.
     */
    public function index(): View
    {
        [$period, $start, $end, $customFrom, $customTo] = $this->periodFromRequest();

        $ordersInPeriod = fn (): Builder => Order::query()->whereBetween('created_at', [$start, $end]);

        $stats = [
            'revenue' => (clone $ordersInPeriod())
                ->where('payment_status', PaymentStatus::Paid)
                ->where('status', '!=', OrderStatus::Cancelled)
                ->sum('total'),
            'orders_total' => (clone $ordersInPeriod())->count(),
            'action_needed' => (clone $ordersInPeriod())
                ->where(function (Builder $query): void {
                    $query->where('status', OrderStatus::PendingConfirmation)
                        ->orWhere('payment_status', PaymentStatus::PendingVerification);
                })->count(),
            'completed' => (clone $ordersInPeriod())->where('status', OrderStatus::Completed)->count(),
            'cancelled' => (clone $ordersInPeriod())->where('status', OrderStatus::Cancelled)->count(),
            'customers' => User::query()
                ->whereBetween('created_at', [$start, $end])
                ->whereDoesntHave('roles')
                ->count(),
        ];

        return view('admin.dashboard', [
            'user' => auth()->user(),
            'period' => $period,
            'customFrom' => $customFrom,
            'customTo' => $customTo,
            'stats' => $stats,
            'chart' => $this->chartData($period, $start, $end),
            'topProducts' => $this->topProducts($start, $end),
            'recentOrders' => (clone $ordersInPeriod())
                ->latest()
                ->take(8)
                ->get(),
        ]);
    }

    /**
     * Tentukan rentang periode dari input request dengan fallback aman.
     *
     * @return array{0: string, 1: Carbon, 2: Carbon, 3: ?string, 4: ?string}
     */
    private function periodFromRequest(): array
    {
        $period = request()->string('period');

        $period = in_array($period->value, self::PERIODS, true) ? $period->value : 'today';

        if ($period === 'custom') {
            try {
                $from = request()->date('from', 'Y-m-d');
                $to = request()->date('to', 'Y-m-d');
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
     * Susun titik grafik omzet per bucket waktu dalam periode.
     *
     * @return array{points: list<array{label: string, value: float, x: float, y: float}>, max: float, hasData: bool}
     */
    private function chartData(string $period, Carbon $start, Carbon $end): array
    {
        $daily = $period !== 'year';

        $buckets = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $key = $daily ? $cursor->toDateString() : $cursor->format('Y-m');
            $buckets[$key] = 0.0;
            $cursor->add($daily ? 1 : 1, $daily ? 'day' : 'month');
        }

        Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->where('payment_status', PaymentStatus::Paid)
            ->where('status', '!=', OrderStatus::Cancelled)
            ->selectRaw(
                $daily
                    ? 'DATE(created_at) as bucket, SUM(total) as revenue'
                    : $this->monthlyBucketExpression().' as bucket, SUM(total) as revenue'
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
    private function monthlyBucketExpression(): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";
    }

    /**
     * Ambil produk terlaris dari order_items pesanan yang tidak dibatalkan.
     *
     * @return list<array{product_name: string, total_quantity: int, revenue: float}>
     */
    private function topProducts(Carbon $start, Carbon $end): array
    {
        return OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.status', '!=', OrderStatus::Cancelled->value)
            ->where('orders.payment_status', PaymentStatus::Paid->value)
            ->groupBy('order_items.product_name')
            ->selectRaw('order_items.product_name, SUM(order_items.quantity) as total_quantity, SUM(order_items.subtotal) as revenue')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get()
            ->map(fn (OrderItem $item): array => [
                'product_name' => $item->product_name,
                'total_quantity' => (int) $item->total_quantity,
                'revenue' => (float) $item->revenue,
            ])
            ->all();
    }
}
