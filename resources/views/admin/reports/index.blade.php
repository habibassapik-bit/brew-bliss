@extends('layouts.admin')

@section('title', 'Laporan Penjualan')

@section('content')
    @php
        /** @var string $period */
        /** @var ?string $from */
        /** @var ?string $to */
        /** @var array{orders_count: int, items_sold: int, subtotal: float, discount: float, delivery: float, revenue: float, cost: float, gross_profit: float, operational_costs: float, net_profit: float} $summary */
        /** @var array{points: list<array{label: string, value: float, x: float, y: float}>, max: float, hasData: bool} $chart */
        /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, \App\Models\Order> $orders */

        $periodOptions = [
            'today' => 'Hari ini',
            'last7' => '7 hari terakhir',
            'month' => 'Bulan ini',
            'year' => 'Tahun ini',
            'custom' => 'Rentang khusus',
        ];

        $exportQuery = [
            'period' => $period,
            'from' => $from,
            'to' => $to,
        ];
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="heading-1">Laporan Penjualan</h1>
            <p class="mt-1 text-sm text-brand-400">Periode: <span class="font-semibold text-brand-950">{{ $periodLabel }}</span> &middot; dihitung dari pesanan yang sudah dibayar dan tidak dibatalkan.</p>
        </div>
        <a href="{{ route('admin.reports.export', $exportQuery) }}" class="btn btn-secondary">
            &#11015;&#65039; Export Excel
        </a>
    </div>

    <form method="GET" action="{{ route('admin.reports.index') }}" class="card mt-6 p-4 sm:p-5">
        <div class="flex flex-col gap-3 md:flex-row md:items-end">
            <div class="md:w-52">
                <label for="period" class="label">Periode</label>
                <select id="period" name="period" class="input" data-dashboard-period>
                    @foreach ($periodOptions as $value => $label)
                        <option value="{{ $value }}" {{ $period === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary !min-h-0 md:w-28">Terapkan</button>
        </div>
        <div class="mt-3 grid grid-cols-2 gap-3 {{ $period === 'custom' ? '' : 'hidden' }}" data-custom-range>
            <div>
                <label for="from" class="label">Dari</label>
                <input id="from" name="from" type="date" value="{{ $from }}" class="input">
            </div>
            <div>
                <label for="to" class="label">Sampai</label>
                <input id="to" name="to" type="date" value="{{ $to }}" class="input">
            </div>
        </div>
    </form>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-admin.stat-card
            label="Omzet"
            value="Rp {{ number_format($summary['revenue'], 0, ',', '.') }}"
            tone="success"
            hint="Total transaksi dibayar (termasuk ongkir), tanpa pesanan batal" />
        <x-admin.stat-card
            label="Jumlah Pesanan"
            value="{{ number_format($summary['orders_count'], 0, ',', '.') }}"
            tone="neutral"
            hint="Pesanan valid pada periode ini" />
        <x-admin.stat-card
            label="Produk Terjual"
            value="{{ number_format($summary['items_sold'], 0, ',', '.') }} item"
            tone="neutral"
            hint="Jumlah unit dari pesanan valid" />
    </div>

    <div class="card mt-6 p-5">
        <h2 class="heading-3">Perhitungan Keuntungan</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-admin.stat-card
                label="Modal (HPP)"
                value="Rp {{ number_format($summary['cost'], 0, ',', '.') }}"
                tone="warning"
                hint="HPP snapshot &times; qty" />
            <x-admin.stat-card
                label="Laba Kotor"
                value="Rp {{ number_format($summary['gross_profit'], 0, ',', '.') }}"
                tone="{{ $summary['gross_profit'] >= 0 ? 'success' : 'danger' }}"
                hint="Omzet &minus; modal (HPP)" />
            <x-admin.stat-card
                label="Biaya Operasional"
                value="Rp {{ number_format($summary['operational_costs'], 0, ',', '.') }}"
                tone="warning"
                hint="Biaya tercatat pada periode ini" />
            <x-admin.stat-card
                label="Laba Bersih"
                value="Rp {{ number_format($summary['net_profit'], 0, ',', '.') }}"
                tone="{{ $summary['net_profit'] >= 0 ? 'success' : 'danger' }}"
                hint="Laba kotor &minus; biaya operasional" />
        </div>
        <p class="mt-4 text-xs text-brand-400">
            Rumus: Laba kotor = Omzet &minus; Modal (HPP) &middot; Laba bersih = Laba kotor &minus; Biaya operasional.
            Total diskon periode ini: Rp {{ number_format($summary['discount'], 0, ',', '.') }} &middot;
            Total ongkir: Rp {{ number_format($summary['delivery'], 0, ',', '.') }} (sudah termasuk dalam omzet).
        </p>
    </div>

    <div class="card mt-6 p-5">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="heading-3">Grafik Omzet</h2>
            @if ($chart['hasData'])
                <p class="text-xs text-brand-400">Maksimum: Rp {{ number_format($chart['max'], 0, ',', '.') }}</p>
            @endif
        </div>

        @if ($chart['hasData'])
            <div class="mt-4 w-full overflow-hidden">
                @php
                    $points = $chart['points'];
                    $first = $points[0];
                    $last = $points[count($points) - 1];
                    $polyline = collect($points)->map(fn ($point) => $point['x'].','.$point['y'])->implode(' ');
                @endphp
                <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="h-48 w-full sm:h-64" role="img" aria-label="Grafik omzet selama periode terpilih">
                    <defs>
                        <linearGradient id="report-chart-fill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#C77B3F" stop-opacity="0.35"></stop>
                            <stop offset="100%" stop-color="#C77B3F" stop-opacity="0.02"></stop>
                        </linearGradient>
                    </defs>
                    <line x1="0" y1="0" x2="100" y2="0" class="stroke-brand-100" stroke-width="0.5" vector-effect="non-scaling-stroke"></line>
                    <line x1="0" y1="50" x2="100" y2="50" class="stroke-brand-100" stroke-width="0.5" vector-effect="non-scaling-stroke" stroke-dasharray="2 2"></line>
                    <line x1="0" y1="100" x2="100" y2="100" class="stroke-brand-100" stroke-width="0.5" vector-effect="non-scaling-stroke"></line>
                    <polygon points="0,100 {{ $polyline }} 100,100" fill="url(#report-chart-fill)"></polygon>
                    <polyline points="{{ $polyline }}" fill="none" class="stroke-brand-700" stroke-width="2" vector-effect="non-scaling-stroke" stroke-linejoin="round" stroke-linecap="round"></polyline>
                    @foreach ($points as $point)
                        @if ($point['value'] > 0)
                            <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="0.8" class="fill-brand-700" vector-effect="non-scaling-stroke"></circle>
                        @endif
                    @endforeach
                </svg>
                <div class="mt-2 flex justify-between text-xs text-brand-400">
                    <span>
                        {{ $period === 'year' ? 'Jan '.$first['label'] : $first['label'] }}
                        &middot; Rp {{ number_format((float) $first['value'], 0, ',', '.') }}
                    </span>
                    <span>
                        {{ $period === 'year' ? 'Des '.$last['label'] : $last['label'] }}
                        &middot; Rp {{ number_format((float) $last['value'], 0, ',', '.') }}
                    </span>
                </div>
            </div>
        @else
            <x-partials.empty-state
                icon="&#128202;"
                title="Belum ada transaksi pada periode ini"
                description="Grafik akan muncul setelah ada pesanan yang dibayar."
                class="mt-4" />
        @endif
    </div>

    <div class="mt-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="heading-3">Detail Transaksi</h2>
            <p class="mt-1 text-sm text-brand-400">Daftar pesanan valid sesuai periode terpilih.</p>
        </div>
    </div>

    @if ($orders->isEmpty())
        <x-partials.empty-state
            icon="&#129380;"
            title="Belum ada transaksi"
            description="Coba pilih periode lain atau tambah pesanan baru."
            class="mt-4" />
    @else
        <div class="card mt-4">
            <div class="table-scroll">
                <table class="w-full min-w-[820px] text-left text-sm">
                    <thead class="border-b border-brand-100 text-xs uppercase tracking-wide text-brand-400">
                        <tr>
                            <th class="px-4 py-3 font-medium">Nomor</th>
                            <th class="px-4 py-3 font-medium">Tanggal</th>
                            <th class="px-4 py-3 font-medium">Customer</th>
                            <th class="px-4 py-3 font-medium">Item</th>
                            <th class="px-4 py-3 font-medium">Diskon</th>
                            <th class="px-4 py-3 font-medium">Total</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-50">
                        @foreach ($orders as $order)
                            <tr>
                                <td class="px-4 py-3 font-mono font-bold text-brand-950">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="hover:underline">{{ $order->order_number }}</a>
                                </td>
                                <td class="px-4 py-3 text-brand-400">{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</td>
                                <td class="px-4 py-3 text-brand-950">{{ $order->customer_name }}</td>
                                <td class="px-4 py-3 text-brand-950">{{ $order->items_count }}</td>
                                <td class="px-4 py-3 text-brand-400">{{ (float) $order->discount_amount > 0 ? 'Rp '.number_format((float) $order->discount_amount, 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 font-semibold text-brand-950">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <x-partials.order-status-badge :status="$order->status" />
                                        <x-partials.payment-status-badge :status="$order->payment_status" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-brand-50 border-t border-brand-100 md:hidden">
                @foreach ($orders as $order)
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2">
                            <a href="{{ route('admin.orders.show', $order) }}" class="font-mono font-bold text-brand-950 hover:underline">{{ $order->order_number }}</a>
                            <span class="font-semibold text-brand-950">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</span>
                        </div>
                        <p class="mt-1 text-sm text-brand-950">{{ $order->customer_name }} &middot; {{ $order->items_count }} item</p>
                        <p class="text-xs text-brand-400">{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</p>
                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                            <x-partials.order-status-badge :status="$order->status" />
                            <x-partials.payment-status-badge :status="$order->payment_status" />
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
@endsection
