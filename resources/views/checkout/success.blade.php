@extends('layouts.app')

@section('title', 'Pesanan Dibuat')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="card p-6 text-center sm:p-8">
            <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-brand-50 text-2xl" aria-hidden="true">&#9989;</span>
            <h1 class="heading-2 mt-4">Pesanan Berhasil Dibuat</h1>
            <p class="mt-2 text-sm text-brand-400">
                Nomor pesanan <span class="font-semibold text-brand-900">{{ $order->order_number }}</span> sedang menunggu konfirmasi penjual.
            </p>
            <div class="mt-4 flex flex-wrap justify-center gap-2">
                <span class="badge badge-warning"><span class="badge-dot"></span> {{ $order->status->label() }}</span>
                <span class="badge badge-neutral"><span class="badge-dot"></span> {{ $order->payment_status->label() }}</span>
                <span class="badge badge-neutral"><span class="badge-dot"></span> {{ $order->fulfillment_type->label() }}</span>
            </div>
        </div>

        <section class="card mt-6 p-5 sm:p-6" aria-labelledby="rincian">
            <h2 id="rincian" class="heading-3">Rincian Pesanan</h2>
            <ul class="mt-4 space-y-3 text-sm">
                @foreach ($order->items as $item)
                    <li class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-medium text-brand-950">{{ $item->product_name }}</p>
                            <p class="text-xs text-brand-400">{{ $item->quantity }} &times; Rp {{ number_format((float) $item->product_price, 0, ',', '.') }} @if ($item->note) &middot; {{ $item->note }} @endif</p>
                        </div>
                        <p class="shrink-0 font-semibold text-brand-900">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</p>
                    </li>
                @endforeach
            </ul>
            <dl class="mt-4 space-y-2 border-t border-dashed border-brand-100 pt-4 text-sm">
                <div class="flex justify-between">
                    <dt class="text-brand-400">Subtotal</dt>
                    <dd class="font-semibold text-brand-950">Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</dd>
                </div>
                @if ((float) $order->discount_amount > 0)
                    <div class="flex justify-between">
                        <dt class="text-brand-400">Diskon</dt>
                        <dd class="font-semibold text-brand-700">&minus; Rp {{ number_format((float) $order->discount_amount, 0, ',', '.') }}</dd>
                    </div>
                @endif
                @if ((float) $order->delivery_fee > 0)
                    <div class="flex justify-between">
                        <dt class="text-brand-400">Ongkir</dt>
                        <dd class="font-semibold text-brand-950">Rp {{ number_format((float) $order->delivery_fee, 0, ',', '.') }}</dd>
                    </div>
                @endif
                <div class="flex justify-between border-t border-dashed border-brand-100 pt-3 text-base">
                    <dt class="font-bold text-brand-950">Total</dt>
                    <dd class="font-bold text-brand-900">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</dd>
                </div>
            </dl>

            @if ($order->fulfillment_type === \App\Enums\FulfillmentType::Delivery && $order->delivery_address)
                <div class="mt-4 rounded-xl bg-brand-50 p-3 text-sm">
                    <p class="font-semibold text-brand-950">Alamat Pengiriman</p>
                    <p class="mt-1 whitespace-pre-line text-brand-400">{{ $order->delivery_address }}</p>
                </div>
            @endif

            @if ($order->note)
                <div class="mt-4 rounded-xl bg-brand-50 p-3 text-sm">
                    <p class="font-semibold text-brand-950">Catatan</p>
                    <p class="mt-1 text-brand-400">{{ $order->note }}</p>
                </div>
            @endif
        </section>

        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="{{ route('orders.show', $order) }}" class="btn btn-primary">Lihat Detail &amp; Pembayaran</a>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Lanjut Belanja</a>
        </div>
        <p class="mt-4 text-center text-xs text-brand-400">
            Riwayat pesanan juga dapat diakses lewat menu <span class="font-semibold">Pesanan</span>.
        </p>
    </div>
@endsection
