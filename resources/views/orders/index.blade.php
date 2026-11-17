@extends('layouts.app')

@section('title', 'Pesanan Saya')

@section('content')
    <h1 class="heading-1">Pesanan Saya</h1>
    <p class="mt-1 text-sm text-brand-400">Riwayat pesanan yang pernah kamu buat.</p>

    @if ($orders->isEmpty())
        <x-partials.empty-state
            icon="&#129380;"
            title="Belum ada pesanan"
            description="Pesanan yang kamu buat akan muncul di sini."
            actionHref="{{ route('products.index') }}"
            actionLabel="Mulai Belanja"
            class="mt-6" />
    @else
        <div class="mt-6 space-y-4">
            @foreach ($orders as $order)
                <article class="card p-4 sm:p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-mono text-sm font-bold text-brand-950">{{ $order->order_number }}</p>
                            <p class="mt-0.5 text-xs text-brand-400">
                                {{ $order->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-partials.order-status-badge :status="$order->status" />
                            <x-partials.payment-status-badge :status="$order->payment_status" />
                        </div>
                    </div>

                    <p class="mt-3 text-sm text-brand-400">
                        {{ $order->items_count }} produk &middot; {{ $order->fulfillment_type->label() }}
                    </p>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-dashed border-brand-100 pt-3">
                        <p class="text-sm">
                            <span class="text-brand-400">Total:</span>
                            <span class="font-bold text-brand-900">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</span>
                        </p>
                        <a href="{{ route('orders.show', $order) }}" class="btn btn-secondary !min-h-0 px-4 py-2 text-xs">
                            Lihat Detail
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
@endsection
