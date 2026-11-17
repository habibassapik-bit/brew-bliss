@extends('layouts.app')

@section('title', 'Wishlist')

@section('content')
    <h1 class="heading-1">Wishlist</h1>
    <p class="mt-1 text-sm text-brand-400">Produk favorit yang kamu simpan.</p>

    @if ($items->isEmpty())
        <x-partials.empty-state
            icon="&#128150;"
            title="Wishlist kamu masih kosong"
            description="Simpan produk favorit dengan menekan ikon hati di katalog atau halaman produk."
            actionHref="{{ route('products.index') }}"
            actionLabel="Lihat Katalog"
            class="mt-8" />
    @else
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            @foreach ($items as $item)
                @php
                    /** @var \App\Models\Product|null $product */
                    $product = $item->product;
                    $available = $product !== null
                        && $product->deleted_at === null
                        && $product->status !== \App\Enums\ProductStatus::Inactive;
                @endphp
                <div class="card flex gap-4 p-4">
                    <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-gradient-to-br from-brand-100 to-stone-200">
                        @if ($product?->coverImage())
                            <img src="{{ $product->coverImage() }}" alt="{{ $product->name }}" class="h-full w-full object-cover" loading="lazy">
                        @else
                            <span class="grid h-full w-full place-items-center text-2xl font-semibold text-brand-500/70">{{ mb_strtoupper(mb_substr($product?->name ?? '?', 0, 1)) }}</span>
                        @endif
                    </div>

                    <div class="flex min-w-0 flex-1 flex-col">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-xs font-medium uppercase tracking-wide text-brand-500">{{ $product?->category?->name }}</p>
                                <h2 class="mt-0.5 truncate text-sm font-semibold text-brand-950 sm:text-base">
                                    @if ($product !== null && $product->deleted_at === null)
                                        <a href="{{ route('products.show', $product) }}" class="hover:text-brand-700">{{ $product->name }}</a>
                                    @else
                                        {{ $product?->name ?? 'Produk tidak tersedia' }}
                                    @endif
                                </h2>
                            </div>
                            @if ($available)
                                @if ($product->isOrderable())
                                    <span class="badge badge-success"><span class="badge-dot"></span> Tersedia</span>
                                @else
                                    <span class="badge badge-warning"><span class="badge-dot"></span> {{ $product->stock === 0 ? 'Habis' : $product->status->label() }}</span>
                                @endif
                            @else
                                <span class="badge badge-neutral"><span class="badge-dot"></span> Nonaktif</span>
                            @endif
                        </div>

                        <p class="mt-1 text-base font-bold text-brand-900">Rp {{ number_format((float) ($product->price ?? 0), 0, ',', '.') }}</p>

                        <div class="mt-auto flex flex-wrap items-center gap-2 pt-3">
                            @if ($available && $product->isOrderable())
                                <form method="POST" action="{{ route('wishlist.moveToCart', $item) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary !min-h-0 px-3 py-1.5 text-xs">Pindahkan ke Keranjang</button>
                                </form>
                            @elseif ($available)
                                <button type="button" class="btn btn-disabled !min-h-0 px-3 py-1.5 text-xs" disabled>Pindahkan ke Keranjang</button>
                            @endif
                            <form method="POST" action="{{ route('wishlist.destroy', $item) }}"
                                  onsubmit="return confirm('Hapus {{ $product?->name ?? 'produk ini' }} dari wishlist?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger !min-h-0 px-3 py-1.5 text-xs">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
