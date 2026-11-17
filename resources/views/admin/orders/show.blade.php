@extends('layouts.admin')

@section('title', 'Detail Pesanan')

@php
    $payment = $order->payment;
@endphp

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('admin.orders.index') }}" class="text-xs font-medium text-brand-500 hover:text-brand-900 hover:underline">&larr; Kembali ke Daftar Pesanan</a>
            <h1 class="heading-1 mt-1 font-mono">{{ $order->order_number }}</h1>
            <p class="mt-1 text-sm text-brand-400">
                {{ $order->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB &middot;
                Pelanggan: <span class="font-semibold text-brand-950">{{ $order->user?->name ?? $order->customer_name }}</span>
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-partials.order-status-badge :status="$order->status" />
            <x-partials.payment-status-badge :status="$order->payment_status" />
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="card p-5" aria-labelledby="rincian-produk">
                <h2 id="rincian-produk" class="heading-3">Rincian Produk (Snapshot)</h2>
                <div class="table-scroll mt-4">
                    <table class="w-full min-w-[560px] text-left text-sm">
                        <thead class="border-b border-brand-100 text-xs uppercase tracking-wide text-brand-400">
                            <tr>
                                <th class="py-2 pr-4 font-medium">Produk</th>
                                <th class="py-2 pr-4 font-medium">Qty</th>
                                <th class="py-2 pr-4 font-medium">Harga</th>
                                <th class="py-2 pr-4 font-medium">HPP</th>
                                <th class="py-2 text-right font-medium">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-50">
                            @foreach ($order->items as $item)
                                <tr>
                                    <td class="py-2.5 pr-4">
                                        <p class="font-medium text-brand-950">{{ $item->product_name }}</p>
                                        @if ($item->note)
                                            <p class="text-xs text-brand-400">Catatan: {{ $item->note }}</p>
                                        @endif
                                    </td>
                                    <td class="py-2.5 pr-4 text-brand-950">{{ $item->quantity }}</td>
                                    <td class="py-2.5 pr-4 text-brand-400">Rp {{ number_format((float) $item->product_price, 0, ',', '.') }}</td>
                                    <td class="py-2.5 pr-4 text-brand-400">Rp {{ number_format((float) $item->product_cost, 0, ',', '.') }}</td>
                                    <td class="py-2.5 text-right font-semibold text-brand-900">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
            </section>

            <section class="card p-5" aria-labelledby="pembayaran">
                <h2 id="pembayaran" class="heading-3">Pembayaran</h2>

                @if ($payment === null)
                    <p class="mt-3 text-sm text-brand-400">Belum ada data pembayaran.</p>
                @else
                    <dl class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-brand-400">Metode</dt>
                            <dd class="font-semibold text-brand-950">{{ $payment->method->label() }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-brand-400">Jumlah</dt>
                            <dd class="font-semibold text-brand-950">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-brand-400">Status</dt>
                            <dd><x-partials.payment-status-badge :status="$payment->status" /></dd>
                        </div>
                        @if ($payment->payer_name)
                            <div class="flex justify-between gap-3">
                                <dt class="text-brand-400">Nama Pengirim</dt>
                                <dd class="text-brand-950">{{ $payment->payer_name }}</dd>
                            </div>
                        @endif
                        @if ($payment->reference_number)
                            <div class="flex justify-between gap-3">
                                <dt class="text-brand-400">No. Referensi</dt>
                                <dd class="text-brand-950">{{ $payment->reference_number }}</dd>
                            </div>
                        @endif
                        @if ($payment->verifiedBy)
                            <div class="flex justify-between gap-3">
                                <dt class="text-brand-400">Diverifikasi</dt>
                                <dd class="text-brand-950">
                                    {{ $payment->verifiedBy->name }} &middot;
                                    {{ optional($payment->verified_at)->timezone('Asia/Jakarta')->format('d M Y, H:i') }}
                                </dd>
                            </div>
                        @endif
                        @if ($payment->rejection_reason)
                            <div class="rounded-xl bg-red-50 p-3 text-red-700">
                                <p class="font-semibold">Alasan Penolakan</p>
                                <p class="mt-1">{{ $payment->rejection_reason }}</p>
                            </div>
                        @endif
                    </dl>

                    @if ($payment->proof_path)
                        <a href="{{ route('payments.proof', $order) }}" target="_blank" rel="noopener"
                           class="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-brand-700 hover:underline">
                            &#128247; Lihat Bukti Pembayaran
                        </a>
                    @else
                        <p class="mt-3 text-xs text-brand-400">Belum ada bukti pembayaran diunggah.</p>
                    @endif

                    @if (in_array($payment->status, [\App\Enums\PaymentStatus::Unpaid, \App\Enums\PaymentStatus::PendingVerification], true))
                        <div class="mt-4 flex flex-col gap-3 border-t border-dashed border-brand-100 pt-4 sm:flex-row">
                            <form method="POST" action="{{ route('admin.payments.approve', $payment) }}" class="flex-1">
                                @csrf
                                <input type="hidden" name="note" value="">
                                <button type="submit" class="btn btn-primary w-full"
                                        onclick="return confirm('Setujui pembayaran untuk pesanan {{ $order->order_number }}?')">
                                    Setujui Pembayaran
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="flex-1 space-y-2">
                                @csrf
                                <input type="text" name="rejection_reason" required minlength="5" maxlength="255"
                                       placeholder="Alasan penolakan" aria-label="Alasan penolakan" class="input !mt-0">
                                <button type="submit" class="btn btn-danger w-full border border-red-200"
                                        onclick="return confirm('Tolak pembayaran untuk pesanan {{ $order->order_number }}?')">
                                    Tolak Pembayaran
                                </button>
                            </form>
                        </div>
                    @endif
                @endif
            </section>

            <section class="card p-5" aria-labelledby="pelanggan">
                <h2 id="pelanggan" class="heading-3">Informasi Pelanggan &amp; Pengiriman</h2>
                <dl class="mt-3 space-y-2 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-brand-400">Nama</dt>
                        <dd class="text-brand-950">{{ $order->customer_name }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-brand-400">Telepon</dt>
                        <dd class="text-brand-950">{{ $order->customer_phone }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-brand-400">Metode</dt>
                        <dd><span class="badge badge-neutral"><span class="badge-dot"></span> {{ $order->fulfillment_type->label() }}</span></dd>
                    </div>
                </dl>
                @if ($order->delivery_address)
                    <div class="mt-3 rounded-xl bg-brand-50 p-3 text-sm">
                        <p class="font-semibold text-brand-950">Alamat Pengiriman</p>
                        <p class="mt-1 whitespace-pre-line text-brand-400">{{ $order->delivery_address }}</p>
                    </div>
                @endif
                @if ($order->note)
                    <div class="mt-3 rounded-xl bg-brand-50 p-3 text-sm">
                        <p class="font-semibold text-brand-950">Catatan Pesanan</p>
                        <p class="mt-1 text-brand-400">{{ $order->note }}</p>
                    </div>
                @endif
                @if ($order->cancellation_reason)
                    <div class="mt-3 rounded-xl bg-red-50 p-3 text-sm text-red-700">
                        <p class="font-semibold">Alasan Pembatalan</p>
                        <p class="mt-1">{{ $order->cancellation_reason }}</p>
                    </div>
                @endif
            </section>
        </div>

        <aside class="space-y-6">
            <section class="card p-5" aria-labelledby="ubah-status">
                <h2 id="ubah-status" class="heading-3">Ubah Status Pesanan</h2>

                @if ($order->allowedTransitions() === [])
                    <p class="mt-3 text-sm text-brand-400">Pesanan ini sudah berstatus final ({{ $order->status->label() }}) dan tidak dapat diubah.</p>
                @else
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="mt-3 space-y-3">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label for="status" class="label">Status baru</label>
                            <select id="status" name="status" required class="input mt-1">
                                @foreach ($order->allowedTransitions() as $target)
                                    <option value="{{ $target->value }}">{{ $target->label() }}</option>
                                @endforeach
                            </select>
                            <p class="error-text !mt-1 !text-brand-400">Hanya perpindahan status yang valid.</p>
                        </div>
                        <div>
                            <label for="note" class="label">Catatan (opsional)</label>
                            <input id="note" name="note" type="text" maxlength="255" class="input">
                        </div>
                        <button type="submit" class="btn btn-primary w-full">Simpan Status</button>
                    </form>
                @endif
            </section>

            @if (in_array($order->status, [\App\Enums\OrderStatus::PendingConfirmation, \App\Enums\OrderStatus::AwaitingPayment, \App\Enums\OrderStatus::Processing, \App\Enums\OrderStatus::ReadyPickup, \App\Enums\OrderStatus::Shipping], true))
                <section class="card p-5" aria-labelledby="batal-admin">
                    <h2 id="batal-admin" class="heading-3">Batalkan Pesanan</h2>
                    <p class="mt-2 text-xs text-brand-400">Stok produk akan dikembalikan ke katalog.</p>
                    <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" class="mt-3 space-y-3"
                          onsubmit="return confirm('Batalkan pesanan {{ $order->order_number }}?')">
                        @csrf
                        <div>
                            <label for="cancellation_reason" class="label">Alasan pembatalan</label>
                            <textarea id="cancellation_reason" name="cancellation_reason" rows="2" required minlength="5" maxlength="255" class="input"></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-full border border-red-200">Batalkan Pesanan</button>
                    </form>
                </section>
            @endif

            <section class="card p-5" aria-labelledby="riwayat">
                <h2 id="riwayat" class="heading-3">Riwayat Status</h2>

                @if ($order->statusHistories->isEmpty())
                    <p class="mt-3 text-sm text-brand-400">Belum ada riwayat status.</p>
                @else
                    <ol class="mt-4 space-y-0">
                        @foreach ($order->statusHistories->sortByDesc('created_at')->values() as $history)
                            <li class="border-l-2 border-brand-100 pb-4 pl-3 last:pb-0">
                                <p class="text-sm font-semibold text-brand-950">{{ $history->status->label() }}</p>
                                <p class="text-xs text-brand-400">
                                    {{ $history->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
                                    @if ($history->changedBy) &middot; oleh {{ $history->changedBy->name }} @endif
                                </p>
                                @if ($history->note)
                                    <p class="mt-0.5 text-xs text-brand-400">{{ $history->note }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>
        </aside>
    </div>
@endsection
