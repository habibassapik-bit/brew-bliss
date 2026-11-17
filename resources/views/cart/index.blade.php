@extends('layouts.app')

@section('title', 'Keranjang')

@section('content')
    <h1 class="heading-1">Keranjang</h1>
    <p class="mt-1 text-sm text-brand-400">Periksa pesananmu sebelum melanjutkan ke checkout.</p>

    @php
        $invalidLines = collect($lines)->where('orderable', false);
        $canCheckout = $lines->isNotEmpty() && $invalidLines->isEmpty();
        $itemCount = collect($lines)->sum(fn ($line) => $line['item']->quantity);
    @endphp
 
    @if ($lines->isEmpty())
        <x-partials.empty-state
            icon="&#128722;"
            title="Keranjangmu masih kosong"
            description="Yuk, lihat produk menarik di katalog kami."
            actionHref="{{ route('products.index') }}"
            actionLabel="Lihat Katalog"
            class="mt-6" />
    @else
        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <ul class="space-y-4 lg:col-span-2">
                @foreach ($lines as $line)
                    @php
                        /** @var \App\Models\CartItem $item */
                        $item = $line['item'];
                        $product = $line['product'];
                        $orderable = $line['orderable'];
                        $productName = $product?->name ?? 'Produk tidak tersedia';
                        $statusLabel = $product === null
                            ? 'Produk dihapus'
                            : ($product->status !== \App\Enums\ProductStatus::Available
                                ? $product->status->label()
                                : ($product->stock < $item->quantity ? 'Stok berkurang' : ''));
                        $stockIssue = $product !== null && $product->status === \App\Enums\ProductStatus::Available && $product->stock < $item->quantity;
                    @endphp
                    <li class="card p-4">
                        <div class="flex gap-4">
                            <a href="{{ $product !== null ? route('products.show', $product) : '#' }}" @unless($product !== null) onclick="return false" @endunless class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-gradient-to-br from-brand-100 to-stone-200">
                                @if ($product?->coverImage())
                                    <img src="{{ $product->coverImage() }}" alt="{{ $productName }}" class="h-full w-full object-cover" loading="lazy">
                                @else
                                    <span class="grid h-full w-full place-items-center text-xl font-semibold text-brand-500/70">{{ mb_strtoupper(mb_substr($productName, 0, 1)) }}</span>
                                @endif
                            </a>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-xs font-medium uppercase tracking-wide text-brand-500">{{ $product?->category?->name }}</p>
                                        @if ($product !== null)
                                            <a href="{{ route('products.show', $product) }}" class="block truncate font-semibold text-brand-950 hover:text-brand-700">{{ $productName }}</a>
                                        @else
                                            <span class="block truncate font-semibold text-brand-950">{{ $productName }}</span>
                                        @endif
                                        @if ($product !== null)
                                            <p class="mt-0.5 text-sm text-brand-400">Rp {{ number_format((float) $product->price, 0, ',', '.') }} / porsi</p>
                                        @endif
                                    </div>
                                    @if ($orderable)
                                        <p class="text-sm font-bold text-brand-900">Rp {{ number_format($line['line_total'], 0, ',', '.') }}</p>
                                    @else
                                        <span class="badge badge-warning"><span class="badge-dot"></span> {{ $statusLabel !== '' ? $statusLabel : 'Tidak tersedia' }}</span>
                                    @endif
                                </div>

                                @if ($item->note)
                                    <p class="mt-1 text-xs text-brand-400">Catatan: {{ $item->note }}</p>
                                @endif

                                @if (! $orderable)
                                    <p class="error-text mt-1">
                                        @if ($stockIssue)
                                            Stok tinggal {{ $product->stock }}. Kurangi jumlahnya untuk melanjutkan.
                                        @elseif ($product === null)
                                            Produk sudah dihapus dan tidak dapat dipesan. Hapus dari keranjang.
                                        @elseif ($product->stock === 0)
                                            Stok produk habis. Hapus dari keranjang atau cek kembali nanti.
                                        @else
                                            Produk sedang tidak dapat dipesan. Hapus dari keranjang untuk melanjutkan.
                                        @endif
                                    </p>
                                @endif

                                <div class="mt-3 flex flex-wrap items-center gap-3">
                                    @if ($orderable)
                                        <form method="POST" action="{{ route('cart.items.update', $item) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <div data-quantity-stepper class="flex items-center gap-2">
                                                <button type="button" data-stepper-minus class="grid h-10 w-10 place-items-center rounded-lg border border-stone-300 text-brand-700 hover:bg-brand-50" aria-label="Kurangi jumlah {{ $productName }}">&minus;</button>
                                                <input name="quantity" type="number" value="{{ $item->quantity }}" min="1" max="{{ $product->stock }}" inputmode="numeric" class="input !mt-0 h-10 w-14 text-center" aria-label="Jumlah {{ $productName }}" required>
                                                <button type="button" data-stepper-plus class="grid h-10 w-10 place-items-center rounded-lg border border-stone-300 text-brand-700 hover:bg-brand-50" aria-label="Tambah jumlah {{ $productName }}">+</button>
                                            </div>
                                            <button type="submit" class="btn btn-secondary !min-h-0 px-3 py-2 text-xs">Perbarui</button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('cart.items.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger !min-h-0 px-3 py-2 text-xs font-medium">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <aside class="card h-fit p-5 lg:sticky lg:top-20">
                <h2 class="heading-3">Ringkasan</h2>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-brand-400">Subtotal ({{ $itemCount }} item)</dt>
                        <dd class="font-semibold text-brand-950">Rp {{ number_format($subtotal, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex justify-between border-t border-dashed border-brand-100 pt-3 text-base">
                        <dt class="font-bold text-brand-950">Total Sementara</dt>
                        <dd class="font-bold text-brand-900">Rp {{ number_format($subtotal, 0, ',', '.') }}</dd>
                    </div>
                </dl>

                @if ($invalidLines->isNotEmpty())
                    <p class="error-text mt-3">
                        Ada {{ $invalidLines->count() }} produk yang tidak dapat dipesan. Hapus item tersebut untuk melanjutkan ke checkout.
                    </p>
                @else
                    <p class="mt-3 text-xs text-brand-400">Ongkir dan voucher dihitung saat checkout. Harga dan stok divalidasi ulang di server.</p>
                @endif

                @if ($canCheckout)
                    <a href="{{ route('checkout.index') }}" class="btn btn-primary mt-4 w-full">Lanjut ke Checkout</a>
                @else
                    <button type="button" disabled class="btn btn-disabled mt-4 w-full" title="Hapus produk yang tidak tersedia untuk melanjutkan">Lanjut ke Checkout</button>
                @endif
                <a href="{{ route('products.index') }}" class="mt-3 block text-center text-sm font-medium text-brand-700 hover:underline">Tambah produk lain</a>
            </aside>
        </div>
    @endif
@endsection
