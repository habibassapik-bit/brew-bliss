@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <nav class="flex items-center gap-2 text-sm text-brand-400" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-brand-900">Beranda</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('products.index') }}" class="hover:text-brand-900">Katalog</a>
        <span aria-hidden="true">/</span>
        <span class="font-medium text-brand-950">{{ $product->name }}</span>
    </nav>

    <div class="mt-6 grid gap-8 md:grid-cols-2">
        <div class="aspect-square overflow-hidden rounded-3xl border border-brand-100 bg-gradient-to-br from-brand-100 to-stone-200">
            @if ($product->coverImage())
                <img src="{{ $product->coverImage() }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
            @else
                <span class="grid h-full w-full place-items-center text-7xl font-semibold text-brand-500/50">{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</span>
            @endif
        </div>

        <div>
            <p class="text-sm font-medium uppercase tracking-wide text-brand-500">{{ $product->category?->name }}</p>
            <h1 class="heading-1 mt-1">{{ $product->name }}</h1>

            <div class="mt-3 flex flex-wrap items-center gap-2">
                @if ($product->isOrderable())
                    <span class="badge badge-success">
                        <span class="badge-dot"></span> Tersedia
                    </span>
                    <span class="text-xs text-brand-400">Stok {{ $product->stock }} porsi</span>
                @else
                    <span class="badge badge-neutral">
                        <span class="badge-dot"></span> {{ $product->stock === 0 ? 'Habis' : $product->status->label() }}
                    </span>
                @endif
                @if ($averageRating > 0)
                    <span class="text-xs font-medium text-brand-500" aria-label="Rata-rata rating {{ number_format($averageRating, 1) }} dari 5">&#9733; {{ number_format($averageRating, 1) }}</span>
                @endif
            </div>

            <p class="mt-4 text-3xl font-bold text-brand-900">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</p>

            @php /** @var bool $wishlisted */ @endphp
            @if (auth()->check())
                <form method="POST" action="{{ route('wishlist.store') }}" class="mt-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button type="submit" class="inline-flex items-center gap-2 text-sm font-medium {{ $wishlisted ? 'text-brand-700' : 'text-brand-400 hover:text-brand-900' }}">
                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="{{ $wishlisted ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                        </svg>
                        {{ $wishlisted ? 'Tersimpan di wishlist' : 'Simpan ke wishlist' }}
                    </button>
                </form>
            @else
                <a href="{{ route('auth.login') }}" class="mt-3 inline-flex items-center gap-2 text-sm font-medium text-brand-400 hover:text-brand-900">
                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                    </svg>
                    Masuk untuk menyimpan ke wishlist
                </a>
            @endif

            <p class="mt-4 text-sm leading-relaxed text-brand-400">{{ $product->description ?? 'Belum ada deskripsi untuk produk ini.' }}</p>

            <div class="card mt-6 p-4">
                @if ($product->isOrderable())
                    <form method="POST" action="{{ route('cart.items.store') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        <label for="quantity" class="label">Jumlah</label>
                        <div data-quantity-stepper class="mt-2 flex items-center gap-3">
                            <button type="button" data-stepper-minus class="grid h-11 w-11 place-items-center rounded-xl border border-stone-300 text-lg font-semibold text-brand-700 hover:bg-brand-50" aria-label="Kurangi jumlah">&minus;</button>
                            <input id="quantity" name="quantity" type="number" value="1" min="1" max="{{ $product->stock }}" inputmode="numeric" class="input !mt-0 h-11 w-16 text-center" required>
                            <button type="button" data-stepper-plus class="grid h-11 w-11 place-items-center rounded-xl border border-stone-300 text-lg font-semibold text-brand-700 hover:bg-brand-50" aria-label="Tambah jumlah">+</button>
                            <span class="text-xs text-brand-400">Maks. {{ $product->stock }}</span>
                        </div>

                        <label for="note" class="label mt-4">Catatan (opsional)</label>
                        <textarea id="note" name="note" rows="2" maxlength="255" placeholder="Contoh: tanpa saus, pedas sedang" class="input"></textarea>

                        <button type="submit" class="btn btn-primary mt-4 w-full">
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272" /></svg>
                            Tambah ke Keranjang
                        </button>
                    </form>
                @else
                    <button type="button" disabled class="btn btn-disabled mt-1 w-full">Tambah ke Keranjang</button>
                    <p class="mt-2 text-center text-xs text-brand-400">
                        @auth
                            Produk ini sedang tidak dapat dipesan.
                        @else
                            <a href="{{ route('auth.login') }}" class="font-medium text-brand-700 hover:underline">Masuk</a> untuk memesan saat stok tersedia kembali.
                        @endauth
                    </p>
                @endif
            </div>
        </div>
    </div>

    <section class="mt-10" aria-labelledby="ulasan-produk">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 id="ulasan-produk" class="heading-2">Ulasan</h2>
                <p class="mt-1 text-sm text-brand-400">
                    @if ($reviewCount > 0)
                        &#9733; {{ number_format($averageRating, 1) }} / 5 &middot; {{ $reviewCount }} ulasan
                    @else
                        Belum ada ulasan untuk produk ini.
                    @endif
                </p>
            </div>
        </div>

        @if (auth()->check())
            @if ($canReview)
                <form method="POST" action="{{ route('reviews.store', $product) }}" class="card mt-4 p-5">
                    @csrf
                    <p class="font-semibold text-brand-950">Tulis Ulasan</p>
                    <p class="mt-1 text-xs text-brand-400">Kamu membeli produk ini pada pesanan yang sudah selesai.</p>

                    <fieldset class="mt-3">
                        <legend class="label">Rating</legend>
                        <div class="mt-1 flex flex-wrap gap-2">
                            @for ($i = 1; $i <= 5; $i++)
                                <label class="flex cursor-pointer items-center gap-1.5 rounded-xl border border-stone-200 px-3 py-2 text-sm has-checked:border-brand-700 has-checked:bg-brand-50">
                                    <input type="radio" name="rating" value="{{ $i }}" class="accent-brand-700" @checked(old('rating') == $i) required>
                                    {{ $i }} &#9733;
                                </label>
                            @endfor
                        </div>
                    </fieldset>

                    <label for="comment" class="label mt-3">Ulasan (opsional)</label>
                    <textarea id="comment" name="comment" rows="3" maxlength="1000" placeholder="Bagaimana pengalamanmu dengan produk ini?" class="input">{{ old('comment') }}</textarea>
                    <button type="submit" class="btn btn-primary mt-3">Kirim Ulasan</button>
                </form>
            @elseif ($ownReview !== null)
                <div class="mt-4">
                    <x-partials.status-banner type="info">
                        Kamu sudah memberikan ulasan untuk produk ini ({{ $ownReview->rating }} &#9733;). Terima kasih!
                    </x-partials.status-banner>
                </div>
            @else
                <div class="mt-4">
                    <x-partials.status-banner type="info">
                        Ulasan hanya dapat diberikan untuk produk dari pesanan yang telah selesai.
                    </x-partials.status-banner>
                </div>
            @endif
        @else
            <p class="mt-4 text-sm text-brand-400">
                <a href="{{ route('auth.login') }}" class="font-medium text-brand-700 hover:underline">Masuk</a>
                untuk menulis ulasan jika kamu pernah membeli produk ini.
            </p>
        @endif

        @if ($reviews->isNotEmpty())
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                @foreach ($reviews as $review)
                    <div class="card p-4">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-medium text-brand-500" aria-label="Rating {{ $review->rating }} dari 5">
                                @for ($i = 1; $i <= 5; $i++)
                                    {{ $i <= $review->rating ? '&#9733;' : '&#9734;' }}
                                @endfor
                            </p>
                            <p class="text-xs text-brand-400">{{ $review->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y') }}</p>
                        </div>
                        <p class="mt-2 text-sm text-stone-700">{{ $review->comment ?? 'Tanpa komentar.' }}</p>
                        <p class="mt-2 text-xs text-brand-400">{{ $review->user?->name }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $reviews->links() }}
            </div>
        @endif
    </section>

    @if ($relatedProducts->isNotEmpty())
        <section class="mt-10" aria-labelledby="produk-terkait">
            <h2 id="produk-terkait" class="heading-2">Produk Serupa</h2>
            <div class="mt-4 grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-4">
                @foreach ($relatedProducts as $product)
                    @include('partials.product-card', ['wishlisted' => in_array($product->id, $wishlistProductIds, true)])
                @endforeach
            </div>
        </section>
    @endif
@endsection
