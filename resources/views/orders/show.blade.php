@extends('layouts.app')

@section('title', 'Detail Pesanan')

@php
    $payment = $order->payment;
    $canUploadProof = $payment !== null
        && $payment->method->requiresProof()
        && in_array($payment->status, [\App\Enums\PaymentStatus::Unpaid, \App\Enums\PaymentStatus::PendingVerification, \App\Enums\PaymentStatus::Rejected], true)
        && ! in_array($order->status, [\App\Enums\OrderStatus::Cancelled, \App\Enums\OrderStatus::Completed], true);
    $bankAccounts = collect(json_decode((string) \App\Models\Setting::get('bank_accounts', '{}'), true)['bank_transfer'] ?? []);
@endphp

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('orders.index') }}" class="text-xs font-medium text-brand-500 hover:text-brand-900 hover:underline">&larr; Kembali ke Pesanan Saya</a>
            <h1 class="heading-1 mt-1 font-mono">{{ $order->order_number }}</h1>
            <p class="mt-1 text-sm text-brand-400">
                Dibuat {{ $order->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
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
                <h2 id="rincian-produk" class="heading-3">Rincian Produk</h2>
                <ul class="mt-4 space-y-3 text-sm">
                    @foreach ($order->items as $item)
                        <li class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-medium text-brand-950">{{ $item->product_name }}</p>
                                <p class="text-xs text-brand-400">
                                    {{ $item->quantity }} &times; Rp {{ number_format((float) $item->product_price, 0, ',', '.') }} @if ($item->note) &middot; {{ $item->note }} @endif
                                </p>
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
            </section>

            <section class="card p-5" aria-labelledby="pengiriman">
                <h2 id="pengiriman" class="heading-3">Metode Pemesanan</h2>
                <p class="mt-3 text-sm text-brand-400">
                    <span class="badge badge-neutral"><span class="badge-dot"></span> {{ $order->fulfillment_type->label() }}</span>
                </p>

                @if ($order->fulfillment_type === \App\Enums\FulfillmentType::Delivery && $order->delivery_address)
                    <div class="mt-3 rounded-xl bg-brand-50 p-3 text-sm">
                        <p class="font-semibold text-brand-950">Alamat Pengiriman</p>
                        <p class="mt-1 whitespace-pre-line text-brand-400">{{ $order->delivery_address }}</p>
                    </div>
                @elseif ($order->fulfillment_type === \App\Enums\FulfillmentType::Pickup)
                    <div class="mt-3 rounded-xl bg-brand-50 p-3 text-sm">
                        <p class="font-semibold text-brand-950">Ambil di Toko</p>
                        <p class="mt-1 text-brand-400">{{ \App\Models\Setting::get('store_address', '-') }}</p>
                    </div>
                @endif

                @if ($order->note)
                    <div class="mt-3 rounded-xl bg-brand-50 p-3 text-sm">
                        <p class="font-semibold text-brand-950">Catatan Pesanan</p>
                        <p class="mt-1 text-brand-400">{{ $order->note }}</p>
                    </div>
                @endif
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
                    @endif

                    @if ($payment->status === \App\Enums\PaymentStatus::Unpaid && ! $canUploadProof)
                        <div class="mt-3">
                            <x-partials.status-banner type="info">
                                {{ $payment->method->label() }} &mdash; bukti pembayaran tidak diperlukan untuk metode ini.
                            </x-partials.status-banner>
                        </div>
                    @endif

                    @if ($canUploadProof)
                        @if ($payment->method === \App\Enums\PaymentMethod::BankTransfer)
                            <div class="mt-4 rounded-xl bg-brand-50 p-3 text-sm">
                                <p class="font-semibold text-brand-950">Instruksi Transfer</p>
                                @if ($bankAccounts->isNotEmpty())
                                    <ul class="mt-1 space-y-1 text-brand-400">
                                        @foreach ($bankAccounts as $account)
                                            <li>{{ collect((array) $account)->implode(' &middot; ') }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="mt-1 text-brand-400">Informasi rekening akan diinformasikan oleh penjual. Hubungi kami jika membutuhkan bantuan.</p>
                                @endif
                            </div>
                        @elseif ($payment->method === \App\Enums\PaymentMethod::Qris || $payment->method === \App\Enums\PaymentMethod::Ewallet)
                            <div class="mt-4 rounded-xl bg-brand-50 p-3 text-sm">
                                <p class="font-semibold text-brand-950">Instruksi Pembayaran</p>
                                <p class="mt-1 text-brand-400">
                                    Lakukan pembayaran {{ $payment->method->label() }} sebesar Rp {{ number_format((float) $payment->amount, 0, ',', '.') }},
                                    lalu unggah bukti pembayaran di bawah. Pembayaran akan diverifikasi oleh penjual.
                                </p>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('payments.store', $order) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                            @csrf
                            <div>
                                <label for="proof" class="label">Bukti Pembayaran {{ $payment->status === \App\Enums\PaymentStatus::Rejected ? '(unggah ulang)' : '' }}</label>
                                <input id="proof" name="proof" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required
                                       class="input mt-1 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-brand-700">
                                <p class="error-text !mt-1 !text-brand-400">Format JPG, PNG, atau WEBP. Maksimal 2 MB.</p>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label for="payer_name" class="label">Nama pengirim (opsional)</label>
                                    <input id="payer_name" name="payer_name" type="text" value="{{ old('payer_name', $payment->payer_name) }}" maxlength="255" class="input">
                                </div>
                                <div>
                                    <label for="reference_number" class="label">No. referensi (opsional)</label>
                                    <input id="reference_number" name="reference_number" type="text" value="{{ old('reference_number', $payment->reference_number) }}" maxlength="100" class="input">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-full sm:w-auto">
                                {{ $payment->status === \App\Enums\PaymentStatus::Rejected ? 'Unggah Ulang Bukti' : 'Kirim Bukti Pembayaran' }}
                            </button>
                        </form>
                    @elseif ($payment->status === \App\Enums\PaymentStatus::PendingVerification)
                        <div class="mt-3">
                            <x-partials.status-banner type="info">
                                Bukti pembayaran sudah diterima dan sedang menunggu verifikasi penjual.
                            </x-partials.status-banner>
                        </div>
                    @endif
                @endif
            </section>
        </div>

        <aside class="space-y-6">
            <section class="card p-5" aria-labelledby="tracking">
                <h2 id="tracking" class="heading-3">Riwayat Status</h2>

                @if ($order->statusHistories->isEmpty())
                    <p class="mt-3 text-sm text-brand-400">Belum ada riwayat status.</p>
                @else
                    <ol class="mt-4 space-y-0">
                        @foreach ($order->statusHistories->sortBy('created_at')->values() as $index => $history)
                            <li class="relative flex gap-3 pb-5 last:pb-0">
                                @if (! $loop->last)
                                    <span class="absolute left-[9px] top-5 h-full w-px bg-brand-100" aria-hidden="true"></span>
                                @endif
                                <span class="mt-0.5 grid h-[19px] w-[19px] shrink-0 place-items-center rounded-full border-2 {{ $loop->last ? 'border-brand-700 bg-brand-700 text-white' : 'border-brand-100 bg-white' }}"
                                      aria-hidden="true">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $loop->last ? 'bg-white' : 'bg-brand-100' }}"></span>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold {{ $loop->last ? 'text-brand-950' : 'text-brand-400' }}">
                                        {{ $history->status->label() }}
                                        @if ($loop->last)
                                            <span class="ml-1 align-middle text-[10px] font-bold uppercase tracking-wide text-brand-500">Status saat ini</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-brand-400">
                                        {{ $history->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
                                    </p>
                                    @if ($history->note)
                                        <p class="mt-0.5 text-xs text-brand-400">{{ $history->note }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>

            @if ($order->canBeCancelledByCustomer())
                <section class="card p-5" aria-labelledby="batal">
                    <h2 id="batal" class="heading-3">Batalkan Pesanan</h2>
                    <p class="mt-2 text-xs text-brand-400">Pesanan yang belum dibayar dapat dibatalkan. Stok produk akan dikembalikan.</p>
                    <form method="POST" action="{{ route('orders.cancel', $order) }}" class="mt-3 space-y-3"
                          onsubmit="return confirm('Batalkan pesanan {{ $order->order_number }}?')">
                        @csrf
                        <div>
                            <label for="cancellation_reason" class="label">Alasan pembatalan</label>
                            <textarea id="cancellation_reason" name="cancellation_reason" rows="2" required minlength="5" maxlength="255"
                                      placeholder="Contoh: berubah pikiran" class="input">{{ old('cancellation_reason') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-full border border-red-200">Batalkan Pesanan</button>
                    </form>
                </section>
            @endif
        </aside>
    </div>
@endsection
