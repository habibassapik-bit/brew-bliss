@props([
    'product',
    'wishlisted' => false,
])

@if (auth()->check())
    <form method="POST" action="{{ route('wishlist.store') }}">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <button type="submit"
                class="absolute top-3 left-3 grid h-8 w-8 place-items-center rounded-full bg-white/90 shadow-sm transition hover:bg-white {{ $wishlisted ? 'text-brand-700' : 'text-stone-400' }}"
                aria-label="{{ $wishlisted ? 'Sudah ada di wishlist' : 'Simpan ke wishlist' }}"
                title="{{ $wishlisted ? 'Sudah ada di wishlist' : 'Simpan ke wishlist' }}">
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="{{ $wishlisted ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
            </svg>
        </button>
    </form>
@else
    <a href="{{ route('auth.login') }}"
       class="absolute top-3 left-3 grid h-8 w-8 place-items-center rounded-full bg-white/90 shadow-sm transition hover:bg-white text-stone-400"
       aria-label="Masuk untuk menyimpan produk"
       title="Masuk untuk menyimpan ke wishlist">
        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
        </svg>
    </a>
@endif
