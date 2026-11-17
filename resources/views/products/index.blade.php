@extends('layouts.app')

@section('title', 'Katalog Produk')

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

{{-- ==================== KONTEN UTAMA (DI ATAS LAYER BACKGROUND) ==================== --}}
<div class="relative z-10">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#EFE4D6]/90 px-3 py-1 text-xs font-semibold text-[#7A5236]">
                ☕ Menu Brew & Bliss
            </span>
            <h1 class="heading-1 mt-2">Katalog Produk</h1>
            <p class="mt-1 text-sm text-brand-400">Temukan cemilan, dessert, dan minuman favoritmu.</p>
        </div>
    </div>

    {{-- Card Pencarian & Filter (1 Form Utuh + Efek Frosted Glass) --}}
    <form method="GET" action="{{ route('products.index') }}"
          class="card mt-6 border border-[#E5D7C6]/80 bg-white/85 p-4 shadow-sm backdrop-blur-md sm:p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
            {{-- Kolom Pencarian --}}
            <div class="relative w-full min-w-0 flex-1">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-brand-400"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input type="search"
                       name="q"
                       value="{{ $search }}"
                       placeholder="Cari nama produk..."
                       aria-label="Cari nama produk"
                       class="input !mt-0 w-full bg-white/95 !pl-10 !pr-4 text-sm sm:text-base">
            </div>

            {{-- Filter Ketersediaan, Urutkan & Tombol Terapkan --}}
            <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center">
                <select name="availability" aria-label="Filter ketersediaan" class="input !mt-0 bg-white/95 sm:w-44">
                    <option value="">Semua ketersediaan</option>
                    <option value="tersedia" {{ $availability === 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                    <option value="habis" {{ $availability === 'habis' ? 'selected' : '' }}>Habis</option>
                </select>
                <select name="sort" aria-label="Urutkan" class="input !mt-0 bg-white/95 sm:w-44">
                    <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Terbaru</option>
                    <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>Terpopuler</option>
                    <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Harga terendah</option>
                    <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Harga tertinggi</option>
                </select>
                <button type="submit" class="btn btn-primary shrink-0">Terapkan</button>
            </div>
        </div>

        <input type="hidden" name="category" value="{{ $categorySlug }}">

        {{-- Pill Filter Kategori --}}
        <div class="mt-4 flex gap-2 overflow-x-auto pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" role="group" aria-label="Filter kategori">
            <a href="{{ route('products.index', array_filter(['q' => $search, 'availability' => $availability, 'sort' => $sort])) }}"
               class="shrink-0 rounded-full border px-3.5 py-1.5 text-sm font-medium transition-colors {{ $categorySlug === '' ? 'border-brand-700 bg-brand-700 text-white shadow-sm' : 'border-stone-200 bg-brand-50/90 text-brand-400 hover:border-brand-500' }}">
                Semua
            </a>
            @foreach ($categories as $category)
                <a href="{{ route('products.index', array_filter(['q' => $search, 'availability' => $availability, 'sort' => $sort, 'category' => $category->slug])) }}"
                   class="shrink-0 rounded-full border px-3.5 py-1.5 text-sm font-medium transition-colors {{ $categorySlug === $category->slug ? 'border-brand-700 bg-brand-700 text-white shadow-sm' : 'border-stone-200 bg-brand-50/90 text-brand-400 hover:border-brand-500' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    </form>

    {{-- Grid Produk --}}
    <div class="mt-6 grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 lg:grid-cols-4">
        @forelse ($products as $product)
            @include('partials.product-card', ['wishlisted' => in_array($product->id, $wishlistProductIds, true)])
        @empty
            <x-partials.empty-state
                icon="&#128269;"
                title="Produk tidak ditemukan"
                description="Coba ubah kata kunci atau filter pencarian."
                class="col-span-full bg-white/85 backdrop-blur-sm" />
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $products->links() }}
    </div>
</div>
@endsection