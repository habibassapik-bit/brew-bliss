<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exports\SalesReportExport;
use App\Http\Controllers\Controller;
use App\Models\OperationalCost;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\ActivityLogger;
use App\Support\ReportPeriod;
use App\Support\RevenueChart;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    /**
     * Tampilkan laporan penjualan, keuntungan, dan grafik sesuai periode.
     * Definisi omzet konsisten dengan Dashboard: paid dan bukan cancelled.
     */
    public function index(): View
    {
        [$period, $start, $end, $from, $to] = ReportPeriod::fromRequest(request());

        return view('admin.reports.index', [
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'periodLabel' => ReportPeriod::label($period, $from, $to),
            'summary' => $this->buildSummary($start, $end),
            'chart' => RevenueChart::build($period, $start, $end),
            'orders' => $this->ordersQuery($start, $end)
                ->orderByDesc('created_at')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    /**
     * Export laporan sesuai filter periode aktif ke file .xlsx.
     */
    public function export(): BinaryFileResponse
    {
        [$period, $start, $end, $from, $to] = ReportPeriod::fromRequest(request());

        $summary = $this->buildSummary($start, $end);
        $orders = $this->ordersQuery($start, $end)
            ->orderByDesc('created_at')
            ->get();

        $filename = 'laporan-penjualan-'
            .($from ?? $start->toDateString())
            .'-sd-'
            .($to ?? $end->toDateString())
            .'.xlsx';

        ActivityLogger::log('report.exported', null, [
            'description' => 'Export laporan penjualan periode '.ReportPeriod::label($period, $from, $to).'.',
            'rows' => $orders->count(),
        ]);

        return Excel::download(new SalesReportExport(
            $this->summaryRows($period, $from, $to, $summary),
            $this->detailRows($orders),
        ), $filename);
    }

    /**
     * Susun ringkasan omzet, modal, dan laba dari data transaksi nyata.
     *
     * @return array{orders_count: int, items_sold: int, subtotal: float, discount: float, delivery: float, revenue: float, cost: float, gross_profit: float, operational_costs: float, net_profit: float}
     */
    private function buildSummary(Carbon $start, Carbon $end): array
    {
        $totals = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->where('payment_status', PaymentStatus::Paid)
            ->where('status', '!=', OrderStatus::Cancelled)
            ->selectRaw('COUNT(*) as orders_count, COALESCE(SUM(subtotal), 0) as subtotal, COALESCE(SUM(discount_amount), 0) as discount, COALESCE(SUM(delivery_fee), 0) as delivery, COALESCE(SUM(total), 0) as revenue')
            ->first();

        $items = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.payment_status', PaymentStatus::Paid->value)
            ->where('orders.status', '!=', OrderStatus::Cancelled->value)
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as quantity, COALESCE(SUM(order_items.product_cost * order_items.quantity), 0) as cost')
            ->first();

        $operationalCosts = (float) OperationalCost::query()
            ->whereBetween('incurred_at', [$start, $end])
            ->sum('amount');

        $revenue = (float) $totals->revenue;
        $cost = (float) $items->cost;
        $grossProfit = $revenue - $cost;

        return [
            'orders_count' => (int) $totals->orders_count,
            'items_sold' => (int) $items->quantity,
            'subtotal' => (float) $totals->subtotal,
            'discount' => (float) $totals->discount,
            'delivery' => (float) $totals->delivery,
            'revenue' => $revenue,
            'cost' => $cost,
            'gross_profit' => $grossProfit,
            'operational_costs' => $operationalCosts,
            'net_profit' => $grossProfit - $operationalCosts,
        ];
    }

    /**
     * Query pesanan valid (paid, bukan cancelled) dalam periode.
     */
    private function ordersQuery(Carbon $start, Carbon $end): Builder
    {
        return Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->where('payment_status', PaymentStatus::Paid)
            ->where('status', '!=', OrderStatus::Cancelled)
            ->withCount('items');
    }

    /**
     * Baris ringkasan untuk sheet pertama file export.
     *
     * @param  array<string, float|int>  $summary
     * @return list<array{0: string, 1: mixed}>
     */
    private function summaryRows(string $period, ?string $from, ?string $to, array $summary): array
    {
        return [
            ['Periode', ReportPeriod::label($period, $from, $to)],
            ['Dicetak', now()->timezone('Asia/Jakarta')->format('d-m-Y H:i').' WIB'],
            ['Jumlah pesanan', $summary['orders_count']],
            ['Produk terjual', $summary['items_sold']],
            ['Subtotal', $this->money((float) $summary['subtotal'])],
            ['Total diskon', $this->money((float) $summary['discount'])],
            ['Total ongkir', $this->money((float) $summary['delivery'])],
            ['Omzet', $this->money((float) $summary['revenue'])],
            ['Modal (HPP)', $this->money((float) $summary['cost'])],
            ['Laba kotor', $this->money((float) $summary['gross_profit'])],
            ['Biaya operasional', $this->money((float) $summary['operational_costs'])],
            ['Laba bersih', $this->money((float) $summary['net_profit'])],
        ];
    }

    /**
     * Baris detail transaksi untuk sheet kedua file export.
     *
     * @param  Collection<int, Order>  $orders
     * @return list<array<string, mixed>>
     */
    private function detailRows(Collection $orders): array
    {
        return $orders->map(fn (Order $order): array => [
            'order_number' => $order->order_number,
            'created_at' => $order->created_at->timezone('Asia/Jakarta')->format('d-m-Y H:i').' WIB',
            'customer_name' => $order->customer_name,
            'items_count' => $order->items_count,
            'subtotal' => $this->money((float) $order->subtotal),
            'discount' => $this->money((float) $order->discount_amount),
            'delivery_fee' => $this->money((float) $order->delivery_fee),
            'total' => $this->money((float) $order->total),
            'status' => $order->status->label(),
            'payment_status' => $order->payment_status->label(),
        ])->all();
    }

    /**
     * Format nominal rupiah yang mudah dibaca.
     */
    private function money(float $value): string
    {
        return 'Rp '.number_format($value, 0, ',', '.');
    }
}
