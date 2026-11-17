@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    @php
        /** @var \App\Models\User $user */
        /** @var string $period */
        /** @var array<string, float|int> $stats */
        /** @var array{points: list<array{label: string, value: float, x: float, y: float}>, max: float, hasData: bool} $chart */
        /** @var list<array{product_name: string, total_quantity: int, revenue: float}> $topProducts */
        /** @var \Illuminate\Support\Collection<int, \App\Models\Order> $recentOrders */

        $periodOptions = [
            'today' => 'Hari ini',
            'last7' => '7 hari terakhir',
            'month' => 'Bulan ini',
            'year' => 'Tahun ini',
            'custom' => 'Rentang khusus',
        ];
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="heading-1">Dashboard Super Admin</h1>
            <p class="mt-1 text-sm text-brand-400">Selamat datang kembali, {{ $user->name }}. Ringkasan toko berdasarkan data nyata.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.dashboard') }}" class="card mt-6 p-4 sm:p-5">
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
                <input id="from" name="from" type="date" value="{{ $customFrom }}" class="input">
            </div>
            <div>
                <label for="to" class="label">Sampai</label>
                <input id="to" name="to" type="date" value="{{ $customTo }}" class="input">
            </div>
        </div>
    </form>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-admin.stat-card
            label="Omzet"
            value="Rp {{ number_format((float) $stats['revenue'], 0, ',', '.') }}"
            tone="success"
            hint="Pesanan yang sudah dibayar pada periode ini" />
        <x-admin.stat-card
            label="Jumlah Pesanan"
            value="{{ $stats['orders_total'] }}"
            hint="Semua pesanan masuk pada periode ini" />
        <x-admin.stat-card
            label="Menunggu Tindakan"
            value="{{ $stats['action_needed'] }}"
            tone="{{ $stats['action_needed'] > 0 ? 'warning' : 'neutral' }}"
            hint="Perlu konfirmasi pesanan atau verifikasi pembayaran" />
        <x-admin.stat-card
            label="Pesanan Selesai"
            value="{{ $stats['completed'] }}"
            tone="success" />
        <x-admin.stat-card
            label="Pesanan Dibatalkan"
            value="{{ $stats['cancelled'] }}"
            tone="{{ $stats['cancelled'] > 0 ? 'danger' : 'neutral' }}" />
        <x-admin.stat-card
            label="Pelanggan Baru"
            value="{{ $stats['customers'] }}"
            hint="Registrasi pelanggan pada periode ini" />
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
                        <linearGradient id="chart-fill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#C77B3F" stop-opacity="0.35"></stop>
                            <stop offset="100%" stop-color="#C77B3F" stop-opacity="0.02"></stop>
                        </linearGradient>
                    </defs>
                    <line x1="0" y1="0" x2="100" y2="0" class="stroke-brand-100" stroke-width="0.5" vector-effect="non-scaling-stroke"></line>
                    <line x1="0" y1="50" x2="100" y2="50" class="stroke-brand-100" stroke-width="0.5" vector-effect="non-scaling-stroke" stroke-dasharray="2 2"></line>
                    <line x1="0" y1="100" x2="100" y2="100" class="stroke-brand-100" stroke-width="0.5" vector-effect="non-scaling-stroke"></line>
                    <polygon points="0,100 {{ $polyline }} 100,100" fill="url(#chart-fill)"></polygon>
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
                        @unless ($first['value'] > 0)
                            &middot; Rp 0
                        @else
                            &middot; Rp {{ number_format((float) $first['value'], 0, ',', '.') }}
                        @endunless
                    </span>
                    <span>
                        {{ $period === 'year' ? 'Des '.$last['label'] : $last['label'] }}
                        @unless ($last['value'] > 0)
                            &middot; Rp 0
                        @else
                            &middot; Rp {{ number_format((float) $last['value'], 0, ',', '.') }}
                        @endunless
                    </span>
                </div>
            </div>
        @else
            <x-partials.empty-state
                icon="&#128200;"
                title="Belum ada omzet pada periode ini"
                description="Grafik akan terisi otomatis setelah ada pesanan yang dibayar."
                class="!py-10" />
        @endif
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="card p-5">
            <h2 class="heading-3">Produk Terlaris</h2>
            @if (count($topProducts) > 0)
                <ol class="mt-4 space-y-3">
                    @foreach ($topProducts as $index => $product)
                        <li class="flex items-center justify-between gap-3 rounded-xl bg-brand-50 px-3 py-2.5">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-brand-700 text-xs font-bold text-white">{{ $index + 1 }}</span>
                                <p class="truncate text-sm font-semibold text-brand-950">{{ $product['product_name'] }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-sm font-bold text-brand-950">{{ $product['total_quantity'] }} terjual</p>
                                <p class="text-xs text-brand-400">Rp {{ number_format($product['revenue'], 0, ',', '.') }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @else
                <x-partials.empty-state
                    icon="&#127871;"
                    title="Belum ada penjualan"
                    description="Produk terlaris akan muncul setelah ada pesanan."
                    class="!py-10" />
            @endif
        </div>

        <div class="card p-5">
            <h2 class="heading-3">Pesanan Terbaru</h2>
            @if ($recentOrders->isNotEmpty())
                <div class="table-scroll mt-4">
                    <table class="w-full min-w-[560px] text-left text-sm">
                        <thead class="border-b border-brand-100 text-xs uppercase tracking-wide text-brand-400">
                            <tr>
                                <th class="px-2 py-2 font-medium">No. Pesanan</th>
                                <th class="px-2 py-2 font-medium">Pelanggan</th>
                                <th class="px-2 py-2 font-medium">Total</th>
                                <th class="px-2 py-2 font-medium">Status</th>
                                <th class="px-2 py-2 font-medium">Pembayaran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-50">
                            @foreach ($recentOrders as $order)
                                <tr>
                                    <td class="px-2 py-2.5 font-semibold text-brand-950">{{ $order->order_number }}</td>
                                    <td class="px-2 py-2.5 text-brand-950">{{ $order->customer_name }}</td>
                                    <td class="px-2 py-2.5 whitespace-nowrap text-brand-950">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</td>
                                    <td class="px-2 py-2.5">
                                        <span class="badge {{ $order->status === \App\Enums\OrderStatus::Completed ? 'badge-success' : ($order->status === \App\Enums\OrderStatus::Cancelled ? 'badge-danger' : 'badge-warning') }}">
                                            <span class="badge-dot"></span> {{ $order->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-2.5">
                                        <span class="badge {{ $order->payment_status === \App\Enums\PaymentStatus::Paid ? 'badge-success' : ($order->payment_status === \App\Enums\PaymentStatus::Rejected ? 'badge-danger' : 'badge-warning') }}">
                                            <span class="badge-dot"></span> {{ $order->payment_status->label() }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 space-y-3 md:hidden">
                    @foreach ($recentOrders as $order)
                        <div class="rounded-xl border border-brand-100 p-3">
                            <div class="flex items-start justify-between gap-2">
                                <p class="font-semibold text-brand-950">{{ $order->order_number }}</p>
                                <span class="badge {{ $order->status === \App\Enums\OrderStatus::Completed ? 'badge-success' : ($order->status === \App\Enums\OrderStatus::Cancelled ? 'badge-danger' : 'badge-warning') }}">
                                    <span class="badge-dot"></span> {{ $order->status->label() }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-brand-950">{{ $order->customer_name }}</p>
                            <div class="mt-2 flex items-center justify-between gap-2 text-xs">
                                <span class="font-bold text-brand-900">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</span>
                                <span class="badge {{ $order->payment_status === \App\Enums\PaymentStatus::Paid ? 'badge-success' : ($order->payment_status === \App\Enums\PaymentStatus::Rejected ? 'badge-danger' : 'badge-warning') }}">
                                    <span class="badge-dot"></span> {{ $order->payment_status->label() }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <x-partials.empty-state
                    icon="&#129505;"
                    title="Belum ada pesanan"
                    description="Pesanan yang masuk akan tampil di sini."
                    class="!py-10" />
            @endif
        </div>
    </div>
@endsection
