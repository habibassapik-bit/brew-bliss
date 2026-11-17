@php /** @var \App\Models\Product $product */ @endphp
@php
    $rawImage = $product->image_url ?? $product->image_path ?? $product->image ?? $product->photo ?? null;
    $imageUrl = $rawImage
        ? (\Illuminate\Support\Str::startsWith($rawImage, ['http://', 'https://']) ? $rawImage : asset('storage/' . ltrim($rawImage, '/')))
        : null;
@endphp

<div class="group relative flex flex-col overflow-hidden rounded-2xl border border-[#E5D7C6]/80 bg-white/90 shadow-sm backdrop-blur-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
    {{-- Area Gambar Produk --}}
    <div class="relative aspect-square w-full overflow-hidden bg-[#F5EDE3]">
        <a href="{{ route('products.show', $product) }}" class="block h-full w-full">
            @if ($imageUrl)
                <img src="{{ $imageUrl }}"
                     alt="{{ $product->name }}"
                     loading="lazy"
                     class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
            @else
                <div class="flex h-full w-full flex-col items-center justify-center bg-gradient-to-br from-[#F3E8DA] to-[#E5D3BE] text-[#8B6B52]">
                    <svg class="h-10 w-10 opacity-60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 8h1a4 4 0 010 8h-1M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8zm4-4h10" />
                    </svg>
                    <span class="mt-1.5 text-[10px] font-medium tracking-wide opacity-75">Brew & Bliss</span>
                </div>
            @endif
        </a>

        {{-- Tombol Wishlist (POST ke wishlist.store) --}}
        <form action="{{ route('wishlist.store') }}" method="POST" class="absolute right-2.5 top-2.5 z-10">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <button type="submit"
                    aria-label="Simpan ke wishlist"
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-stone-600 shadow-sm backdrop-blur-sm transition hover:scale-105 hover:text-rose-500">
                @if (!empty($wishlisted))
                    <svg class="h-4 w-4 fill-rose-500 text-rose-500" viewBox="0 0 24 24">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                @else
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                @endif
            </button>
        </form>
    </div>

    {{-- Detail Produk --}}
    <div class="flex flex-1 flex-col justify-between p-3 sm:p-3.5">
        <div>
            @if ($product->category)
                <span class="inline-block rounded-md bg-[#F5EDE3] px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-[#7A5236]">
                    {{ $product->category->name ?? $product->category }}
                </span>
            @endif

            <h3 class="mt-1.5 line-clamp-2 text-xs font-bold leading-snug text-[#271206] sm:text-sm">
                <a href="{{ route('products.show', $product) }}" class="hover:text-amber-800">
                    {{ $product->name }}
                </a>
            </h3>
        </div>

        <div class="mt-3 flex items-center justify-between gap-2 border-t border-stone-100 pt-2.5">
            <span class="text-xs font-extrabold text-[#271206] sm:text-sm">
                Rp {{ number_format($product->price ?? 0, 0, ',', '.') }}
            </span>

            <a href="{{ route('products.show', $product) }}"
               class="inline-flex items-center justify-center rounded-lg bg-[#4A2C18] px-2.5 py-1 text-[11px] font-semibold text-white transition hover:bg-[#271206]">
                Pesan
            </a>
        </div>
    </div>
</div>
