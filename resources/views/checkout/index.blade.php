@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<style>
    footer {
        position: relative;
        z-index: 10;
    }
</style>

{{-- ==================== DECORATIVE BACKGROUND (WAVES & COFFEE DOODLES) ==================== --}}
<div aria-hidden="true" class="pointer-events-none fixed inset-0 z-0 overflow-hidden">
    {{-- 1. Gelombang Organik di Sudut & Sisi Layar --}}
    <svg class="h-full w-full" viewBox="0 0 1440 900" fill="none" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
        {{-- Sudut Kiri Atas --}}
        <path d="M-60 -40 C160 -30, 290 60, 240 210 C190 360, 60 390, -60 470 Z" fill="#E8DCCB" fill-opacity="0.75"/>
        <path d="M-80 -50 C90 -40, 170 40, 120 150 C70 260, -10 270, -80 320 Z" fill="#D5C0A7" fill-opacity="0.55"/>

        {{-- Sudut Kanan Atas --}}
        <path d="M1500 -40 C1280 -20, 1170 90, 1230 250 C1290 410, 1410 430, 1500 520 Z" fill="#E5D6C3" fill-opacity="0.75"/>
        <path d="M1500 -30 C1340 -10, 1270 60, 1320 175 C1370 290, 1440 300, 1500 350 Z" fill="#C49A80" fill-opacity="0.45"/>

        {{-- Gelombang Tengah Kiri --}}
        <path d="M-70 520 C80 490, 190 560, 160 690 C130 820, 20 860, -70 930 Z" fill="#ECE0D0" fill-opacity="0.8"/>

        {{-- Sudut Kanan Bawah --}}
        <path d="M1500 620 C1330 600, 1210 690, 1250 820 C1280 910, 1400 930, 1500 950 Z" fill="#E6D7C3" fill-opacity="0.7"/>
    </svg>

    {{-- 2. Aksen Line-Art Kopi & Botanikal --}}
    <div class="absolute inset-0 text-[#8B6B52]/[0.24]">
        {{-- Bunga Kiri Tengah --}}
        <svg class="absolute left-[2%] top-[36%] h-14 w-14 -rotate-12 sm:h-16 sm:w-16" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M32 24 C28 12, 36 12, 32 24 Z" />
            <path d="M32 24 C22 14, 14 22, 25 29" />
            <path d="M25 29 C12 29, 12 40, 25 37" />
            <path d="M25 37 C17 48, 27 54, 32 41" />
            <path d="M32 41 C37 54, 47 48, 39 37" />
            <path d="M39 37 C52 40, 52 29, 39 29" />
            <path d="M39 29 C50 22, 42 14, 32 24" />
            <circle cx="32" cy="32" r="3.5" />
        </svg>

        {{-- Daun Atas Kanan --}}
        <svg class="absolute right-[18%] top-[16%] hidden h-12 w-12 -rotate-45 sm:block" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
            <path d="M14 50 C16 26, 32 14, 52 12 C50 32, 38 48, 14 50 Z" />
            <path d="M14 50 L48 16" />
            <path d="M26 38 L24 27 M34 30 L32 20 M28 36 L39 38" />
        </svg>

        {{-- Garis Lengkung Organik Tengah --}}
        <svg class="absolute left-[42%] top-[34%] hidden h-14 w-24 sm:block" viewBox="0 0 100 60" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
            <path d="M8 14 C22 38, 42 42, 54 24 C62 12, 48 6, 44 18 C40 32, 66 48, 92 34" />
        </svg>

        {{-- Biji Kopi Tengah-Kanan --}}
        <svg class="absolute right-[34%] top-[38%] h-11 w-11 rotate-12 sm:h-12 sm:w-12" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">
            <ellipse cx="24" cy="24" rx="11" ry="16" transform="rotate(25 24 24)" />
            <path d="M30 10 C22 18, 26 30, 18 38" />
        </svg>

        {{-- Bunga & Sulur Kanan Tengah --}}
        <svg class="absolute right-[2%] top-[44%] h-14 w-14 rotate-12 sm:h-16 sm:w-16" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
            <path d="M32 24 C22 14, 14 22, 25 29 C12 29, 12 40, 25 37 C17 48, 27 54, 32 41 C37 54, 47 48, 39 37 C52 40, 52 29, 39 29 C50 22, 42 14, 32 24 Z" />
            <circle cx="32" cy="32" r="3" />
            <path d="M46 46 C54 52, 52 60, 42 60" />
        </svg>

        {{-- Biji Kopi Bawah Kiri --}}
        <svg class="absolute bottom-[18%] left-[21%] hidden h-10 w-10 -rotate-12 sm:block" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">
            <ellipse cx="24" cy="24" rx="10" ry="15" transform="rotate(-20 24 24)" />
            <path d="M19 10 C26 19, 22 29, 29 38" />
        </svg>

        <svg class="absolute bottom-[19%] left-[27%] h-11 w-11 rotate-45" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">
            <ellipse cx="24" cy="24" rx="11" ry="16" transform="rotate(25 24 24)" />
            <path d="M30 10 C22 18, 26 30, 18 38" />
        </svg>

        {{-- Biji Kopi Kanan Bawah --}}
        <svg class="absolute bottom-[14%] right-[12%] h-11 w-11 -rotate-12" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">
            <ellipse cx="24" cy="24" rx="11" ry="16" transform="rotate(20 24 24)" />
            <path d="M29 10 C22 18, 26 30, 19 38" />
        </svg>
    </div>
</div>

{{-- ==================== KONTEN UTAMA ==================== --}}
<div class="relative z-10">
    <div>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#EFE4D6]/90 px-3 py-1 text-xs font-semibold text-[#7A5236]">
            🧾 Penyelesaian Pesanan
        </span>
        <h1 class="heading-1 mt-2">Checkout</h1>
        <p class="mt-1 text-sm text-brand-400">Lengkapi data pesanan. Harga, stok, dan diskon divalidasi ulang di server.</p>
    </div>

    @if (! $storeIsOpen)
        <x-partials.status-banner type="error" class="mt-4">
            Toko sedang tutup. Checkout dapat dilakukan saat toko kembali buka. Jam operasional: {{ $storeOpenHours }}.
        </x-partials.status-banner>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        {{-- KOLOM KIRI: FORM DATA PESANAN --}}
        <form id="checkout-form" method="POST" action="{{ route('checkout.store') }}" class="space-y-6 lg:col-span-2">
            @csrf
            <input type="hidden" name="voucher_code" value="{{ $voucher?->code ?? '' }}">

            {{-- 1. Data Pelanggan --}}
            <section class="card rounded-2xl border border-[#E5D7C6]/85 bg-white/90 p-5 shadow-sm backdrop-blur-md sm:p-6" aria-labelledby="data-pelanggan">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#F5EDE3] text-xs font-bold text-[#7A5236]">1</span>
                    <h2 id="data-pelanggan" class="heading-3">Data Pelanggan</h2>
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="customer_name" class="label">Nama lengkap</label>
                        <input id="customer_name" name="customer_name" type="text" value="{{ old('customer_name', $user->name) }}" required maxlength="255" class="input bg-white/95">
                    </div>
                    <div>
                        <label for="customer_phone" class="label">Nomor telepon</label>
                        <input id="customer_phone" name="customer_phone" type="tel" value="{{ old('customer_phone', $user->phone ?? '') }}" required maxlength="20" inputmode="tel" placeholder="08xxxxxxxxxx" class="input bg-white/95">
                    </div>
                </div>
            </section>

            {{-- 2. Metode Pemesanan --}}
            <section class="card rounded-2xl border border-[#E5D7C6]/85 bg-white/90 p-5 shadow-sm backdrop-blur-md sm:p-6" aria-labelledby="metode-pemesanan">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#F5EDE3] text-xs font-bold text-[#7A5236]">2</span>
                    <h2 id="metode-pemesanan" class="heading-3">Metode Pemesanan</h2>
                </div>

                @php
                    $defaultFulfillment = old('fulfillment_type', $deliveryEnabled ? 'delivery' : 'pickup');
                    $selectedAddressChoice = (string) old('address_choice', ($addresses->first()?->id ?? 'new'));
                @endphp

                @if (! $deliveryEnabled && ! $pickupEnabled)
                    <p class="error-text mt-4">Layanan pengiriman dan ambil sendiri sedang tidak tersedia. Silakan cek kembali nanti.</p>
                @endif

                <div class="mt-4 grid gap-3">
                    @if ($deliveryEnabled)
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition {{ $defaultFulfillment === 'delivery' ? 'border-brand-400 bg-brand-50/60' : 'border-stone-200 hover:border-brand-300' }}">
                            <input type="radio" name="fulfillment_type" value="delivery" data-checkout-fulfillment class="mt-1 accent-brand-700" @checked($defaultFulfillment === 'delivery')>
                            <span>
                                <span class="block font-semibold text-brand-950">Pengiriman</span>
                                <span class="block text-xs text-brand-400">Diantar ke alamatmu. Ongkir Rp {{ number_format($deliveryFee, 0, ',', '.') }} per pesanan.</span>
                            </span>
                        </label>
                    @endif

                    @if ($pickupEnabled)
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition {{ $defaultFulfillment === 'pickup' ? 'border-brand-400 bg-brand-50/60' : 'border-stone-200 hover:border-brand-300' }}">
                            <input type="radio" name="fulfillment_type" value="pickup" data-checkout-fulfillment class="mt-1 accent-brand-700" @checked($defaultFulfillment === 'pickup')>
                            <span>
                                <span class="block font-semibold text-brand-950">Ambil Sendiri</span>
                                <span class="block text-xs text-brand-400">Tanpa ongkir. Diambil di toko: {{ $storeAddress }}.</span>
                            </span>
                        </label>
                    @endif
                </div>

                @if ($deliveryEnabled)
                    <div data-delivery-fields class="mt-4 rounded-xl border border-dashed border-[#E5D7C6] p-4">
                        <p class="text-sm font-semibold text-brand-950">Alamat Pengiriman</p>

                        <div class="mt-3 grid gap-2">
                            @forelse ($addresses as $address)
                                <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition {{ $selectedAddressChoice === (string) $address->id ? 'border-brand-400 bg-brand-50/60' : 'border-stone-200 hover:border-brand-300' }}">
                                    <input type="radio" name="address_choice" value="{{ $address->id }}" class="mt-1 accent-brand-700" @checked($selectedAddressChoice === (string) $address->id)>
                                    <span class="min-w-0 text-sm">
                                        <span class="block font-medium text-brand-950">{{ $address->recipient_name }} @if ($address->is_default) <span class="badge badge-warning ml-1"><span class="badge-dot"></span> Utama</span> @endif</span>
                                        <span class="block text-xs text-brand-400">{{ $address->phone }}</span>
                                        <span class="mt-0.5 block text-xs text-brand-400">{{ $address->address }}</span>
                                    </span>
                                </label>
                            @empty
                                <p class="text-xs text-brand-400">Belum ada alamat tersimpan. Isi alamat baru di bawah.</p>
                            @endforelse

                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition {{ $selectedAddressChoice === 'new' ? 'border-brand-400 bg-brand-50/60' : 'border-stone-200 hover:border-brand-300' }}">
                                <input type="radio" name="address_choice" value="new" class="mt-1 accent-brand-700" @checked($selectedAddressChoice === 'new')>
                                <span class="text-sm font-medium text-brand-950">Gunakan alamat baru</span>
                            </label>
                        </div>

                        <div data-new-address-fields class="mt-3 {{ $selectedAddressChoice === 'new' ? '' : 'hidden' }}">
                            <div class="grid gap-3">
                            <div>
                                <label for="recipient_name" class="label">Nama penerima</label>
                                <input id="recipient_name" name="recipient_name" type="text" value="{{ old('recipient_name') }}" maxlength="255" class="input bg-white/95">
                            </div>
                            <div>
                                <label for="address_phone" class="label">Nomor telepon penerima</label>
                                <input id="address_phone" name="address_phone" type="tel" value="{{ old('address_phone') }}" maxlength="20" inputmode="tel" placeholder="08xxxxxxxxxx" class="input bg-white/95">
                            </div>
                            <div>
                                <label for="address" class="label">Alamat lengkap</label>
                                <textarea id="address" name="address" rows="3" maxlength="500" class="input bg-white/95" placeholder="Nama jalan, nomor rumah, kecamatan, kota">{{ old('address') }}</textarea>
                            </div>
                            </div>
                        </div>
                    </div>
                @endif
            </section>

            {{-- 3. Catatan --}}
            <section class="card rounded-2xl border border-[#E5D7C6]/85 bg-white/90 p-5 shadow-sm backdrop-blur-md sm:p-6" aria-labelledby="catatan">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#F5EDE3] text-xs font-bold text-[#7A5236]">3</span>
                    <h2 id="catatan" class="heading-3">Catatan (Opsional)</h2>
                </div>
                <textarea id="note" name="note" rows="3" maxlength="500" class="input mt-4 bg-white/95" placeholder="Contoh: jangan pedas, tanpa es">{{ old('note') }}</textarea>
            </section>

            {{-- 4. Metode Pembayaran --}}
            <section class="card rounded-2xl border border-[#E5D7C6]/85 bg-white/90 p-5 shadow-sm backdrop-blur-md sm:p-6" aria-labelledby="metode-pembayaran">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#F5EDE3] text-xs font-bold text-[#7A5236]">4</span>
                    <h2 id="metode-pembayaran" class="heading-3">Metode Pembayaran</h2>
                </div>

                @php
                    $defaultPaymentMethod = old('payment_method', ($activePaymentMethods[0] ?? \App\Enums\PaymentMethod::BankTransfer)->value);
                @endphp

                <div class="mt-4 grid gap-2">
                    @foreach ($activePaymentMethods as $method)
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition {{ $defaultPaymentMethod === $method->value ? 'border-brand-400 bg-brand-50/60' : 'border-stone-200 hover:border-brand-300' }}">
                            <input type="radio" name="payment_method" value="{{ $method->value }}" class="mt-1 accent-brand-700" @checked($defaultPaymentMethod === $method->value)>
                            <span class="text-sm">
                                <span class="block font-medium text-brand-950">{{ $method->label() }}</span>
                                @if ($method->requiresProof())
                                    <span class="block text-xs text-brand-400">Bukti pembayaran diunggah setelah pesanan dibuat.</span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>

                @php
                    $bankTransferAccounts = collect($bankAccounts['bank_transfer'] ?? []);
                @endphp

                @if ($bankTransferAccounts->isNotEmpty())
                    <div class="mt-3 rounded-xl bg-brand-50 p-3 text-xs text-brand-400">
                        <p class="font-semibold text-brand-950">Rekening Transfer</p>
                        <ul class="mt-1 space-y-1">
                            @foreach ($bankTransferAccounts as $account)
                                <li>{{ collect((array) $account)->implode(' &middot; ') }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </section>

            {{-- 5. Ringkasan & Submit --}}
            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary px-6">Buat Pesanan</button>
            </div>
        </form>

        {{-- KOLOM KANAN: RINGKASAN PESANAN --}}
        <aside data-checkout-summary
               data-subtotal="{{ $subtotal }}"
               data-discount="{{ $discount }}"
               data-delivery-fee="{{ $deliveryFee }}"
               class="card h-fit p-5 lg:sticky lg:top-20">
            <h2 class="heading-3">Ringkasan Pesanan</h2>

            <form method="POST" action="{{ route('checkout.voucher') }}" class="mt-4">
                @csrf
                <label for="voucher_code" class="label">Voucher</label>
                <div class="mt-1 flex gap-2">
                    <input id="voucher_code" name="voucher_code" type="text" value="{{ $voucher?->code ?? old('voucher_code') }}" maxlength="50" placeholder="Masukkan kode voucher" class="input !mt-0 bg-white/95 uppercase">
                    <button type="submit" class="btn btn-secondary !min-h-0 px-3 text-xs">Terapkan</button>
                </div>
                @error('voucher_code')
                    <p class="error-text mt-1">{{ $message }}</p>
                @enderror
            </form>

            <ul class="mt-4 space-y-2 border-t border-dashed border-brand-100 pt-4 text-sm">
                @foreach ($lines as $line)
                    <li class="flex justify-between gap-2">
                        <span class="min-w-0 text-brand-400">
                            <span class="block truncate text-brand-950">{{ $line['product']?->name ?? 'Produk tidak tersedia' }}</span>
                            <span class="text-xs">&times; {{ $line['item']->quantity }}</span>
                        </span>
                        <span class="shrink-0 font-semibold text-brand-950">Rp {{ number_format($line['line_total'], 0, ',', '.') }}</span>
                    </li>
                @endforeach
            </ul>

            <dl class="mt-4 space-y-2 border-t border-dashed border-brand-100 pt-4 text-sm">
                <div class="flex justify-between">
                    <dt class="text-brand-400">Subtotal</dt>
                    <dd class="font-semibold text-brand-950">Rp {{ number_format($subtotal, 0, ',', '.') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-brand-400">Diskon Voucher</dt>
                    <dd class="font-semibold text-emerald-700">&minus; Rp {{ number_format($discount, 0, ',', '.') }}</dd>
                </div>
                <div data-checkout-fee-row class="flex justify-between">
                    <dt class="text-brand-400">Ongkir</dt>
                    <dd class="font-semibold text-brand-950"><span data-checkout-fee>Rp {{ number_format($deliveryFee, 0, ',', '.') }}</span></dd>
                </div>
                <div class="flex justify-between border-t border-brand-100 pt-3 text-base">
                    <dt class="font-bold text-brand-950">Total</dt>
                    <dd class="font-bold text-brand-900" data-checkout-total>Rp {{ number_format(max($subtotal - $discount + $deliveryFee, 0), 0, ',', '.') }}</dd>
                </div>
            </dl>

            <p class="mt-3 text-xs text-brand-400">Harga, stok, dan diskon divalidasi ulang di server saat pesanan dibuat.</p>
        </aside>
    </div>
</div>
@endsection
