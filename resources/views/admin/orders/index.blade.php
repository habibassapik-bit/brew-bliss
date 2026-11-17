@extends('layouts.admin')

@section('title', 'Pesanan')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="heading-1">Pesanan Masuk</h1>
            <p class="mt-1 text-sm text-brand-400">Kelola pesanan dan status pemrosesan.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.orders.index') }}" class="card mt-6 flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
        <input type="search" name="q" value="{{ $search }}" placeholder="Cari nomor pesanan atau nama pelanggan..." aria-label="Cari pesanan" class="input !mt-0 flex-1">
        <select name="status" aria-label="Filter status" class="input !mt-0 sm:w-44">
            <option value="">Semua status</option>
            @foreach ($statuses as $orderStatus)
                <option value="{{ $orderStatus->value }}" {{ $statusFilter === $orderStatus->value ? 'selected' : '' }}>{{ $orderStatus->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-secondary !min-h-0 sm:w-24">Filter</button>
    </form>

    @if ($orders->isEmpty())
        <x-partials.empty-state
            icon="&#128230;"
            title="Pesanan tidak ditemukan"
            description="Belum ada pesanan, atau tidak ada yang cocok dengan filter."
            class="mt-6" />
    @else
        <div class="card mt-6">
            <div class="table-scroll">
                <table class="w-full min-w-[840px] text-left text-sm">
                    <thead class="border-b border-brand-100 text-xs uppercase tracking-wide text-brand-400">
                        <tr>
                            <th class="px-4 py-3 font-medium">No. Pesanan</th>
                            <th class="px-4 py-3 font-medium">Pelanggan</th>
                            <th class="px-4 py-3 font-medium">Tanggal</th>
                            <th class="px-4 py-3 font-medium">Total</th>
                            <th class="px-4 py-3 font-medium">Status Pesanan</th>
                            <th class="px-4 py-3 font-medium">Pembayaran</th>
                            <th class="px-4 py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-50">
                        @foreach ($orders as $order)
                            <tr>
                                <td class="px-4 py-3 font-mono font-semibold text-brand-950">{{ $order->order_number }}</td>
                                <td class="px-4 py-3 text-brand-950">{{ $order->customer_name }}</td>
                                <td class="px-4 py-3 text-brand-400">{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</td>
                                <td class="px-4 py-3 font-semibold text-brand-900">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</td>
                                <td class="px-4 py-3"><x-partials.order-status-badge :status="$order->status" /></td>
                                <td class="px-4 py-3"><x-partials.payment-status-badge :status="$order->payment_status" /></td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-ghost !min-h-0 px-3 py-1.5 text-xs">Detail</a>
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
                            <p class="font-mono text-sm font-bold text-brand-950">{{ $order->order_number }}</p>
                            <x-partials.order-status-badge :status="$order->status" />
                        </div>
                        <p class="mt-1 text-sm text-brand-950">{{ $order->customer_name }}</p>
                        <p class="text-xs text-brand-400">{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</p>
                        <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                            <p class="text-sm font-bold text-brand-900">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</p>
                            <x-partials.payment-status-badge :status="$order->payment_status" />
                        </div>
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-secondary !min-h-0 mt-3 px-4 py-1.5 text-xs">Detail</a>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
@endsection
