@extends('layouts.app')

@section('title', 'Beranda')

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

        {{-- Gelombang Tengah Kiri (Area Kategori & Produk) --}}
        <path d="M-70 520 C80 490, 190 560, 160 690 C130 820, 20 860, -70 930 Z" fill="#ECE0D0" fill-opacity="0.8"/>

        {{-- Sudut Kanan Bawah --}}
        <path d="M1500 620 C1330 600, 1210 690, 1250 820 C1280 910, 1400 930, 1500 950 Z" fill="#E6D7C3" fill-opacity="0.7"/>
    </svg>

    {{-- 2. Aksen Line-Art Kopi & Botanikal --}}
    <div class="absolute inset-0 text-[#8B6B52]/[0.26]">
        {{-- Bunga Kiri Tengah --}}
        <svg class="absolute left-[2%] top-[46%] h-14 w-14 -rotate-12 sm:h-16 sm:w-16" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M32 24 C28 12, 36 12, 32 24 Z" />
            <path d="M32 24 C22 14, 14 22, 25 29" />
            <path d="M25 29 C12 29, 12 40, 25 37" />
            <path d="M25 37 C17 48, 27 54, 32 41" />
            <path d="M32 41 C37 54, 47 48, 39 37" />
            <path d="M39 37 C52 40, 52 29, 39 29" />
            <path d="M39 29 C50 22, 42 14, 32 24" />
            <circle cx="32" cy="32" r="3.5" />
        </svg>

        {{-- Daun Atas Tengah-Kiri --}}
        <svg class="absolute left-[27%] top-[45%] hidden h-12 w-12 rotate-12 sm:block" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
            <path d="M14 50 C16 26, 32 14, 52 12 C50 32, 38 48, 14 50 Z" />
            <path d="M14 50 L48 16" />
            <path d="M26 38 L24 27 M34 30 L32 20 M28 36 L39 38 M36 28 L45 30" />
        </svg>

        {{-- Garis Lengkung Organik Tengah --}}
        <svg class="absolute left-[38%] top-[44%] hidden h-14 w-24 sm:block" viewBox="0 0 100 60" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
            <path d="M8 14 C22 38, 42 42, 54 24 C62 12, 48 6, 44 18 C40 32, 66 48, 92 34" />
        </svg>

        {{-- Biji Kopi Tengah-Kanan --}}
        <svg class="absolute right-[38%] top-[46%] h-11 w-11 rotate-12 sm:h-12 sm:w-12" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">
            <ellipse cx="24" cy="24" rx="11" ry="16" transform="rotate(25 24 24)" />
            <path d="M30 10 C22 18, 26 30, 18 38" />
        </svg>

        {{-- Daun Kanan Atas --}}
        <svg class="absolute right-[24%] top-[44%] hidden h-12 w-12 -rotate-45 sm:block" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
            <path d="M14 50 C16 26, 32 14, 52 12 C50 32, 38 48, 14 50 Z" />
            <path d="M14 50 L48 16" />
            <path d="M26 38 L24 27 M34 30 L32 20 M28 36 L39 38" />
        </svg>

        {{-- Bunga & Sulur Kanan Tengah --}}
        <svg class="absolute right-[2%] top-[43%] h-14 w-14 rotate-12 sm:h-16 sm:w-16" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
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

        {{-- Bunga Kecil Bawah Tengah --}}
        <svg class="absolute bottom-[16%] left-[44%] hidden h-14 w-14 rotate-6 sm:block" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
            <path d="M32 24 C22 14, 14 22, 25 29 C12 29, 12 40, 25 37 C17 48, 27 54, 32 41 C37 54, 47 48, 39 37 C52 40, 52 29, 39 29 C50 22, 42 14, 32 24 Z" />
            <circle cx="32" cy="32" r="3" />
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

    <!-- ==================== 1. HERO SLIDER VIDEO (MOBILE & DESKTOP) ==================== -->
    <section id="hero-slider" class="group relative overflow-hidden rounded-2xl bg-[#1C120C] shadow-lg sm:rounded-3xl">
        <div id="slider-track" class="flex transition-transform duration-500 ease-out">

            <!-- SLIDE 1: MAKANAN -->
            <a href="{{ route('products.index', ['category' => 'makanan']) }}"
               class="relative block aspect-[16/7] w-full flex-shrink-0 overflow-hidden sm:aspect-[1920/416]">
                <video autoplay loop muted playsinline preload="metadata"
                       class="h-full w-full object-cover object-[75%_center] sm:object-center">
                    <source src="{{ asset('videos/video_makanan_5detik.mp4') }}" type="video/mp4">
                </video>

                {{-- Overlay Gelap Lembut di Sisi Kiri Agar Teks Terbaca Jelas --}}
                <div class="absolute inset-0 bg-gradient-to-r from-[#140B06]/85 via-[#140B06]/40 to-transparent"></div>

                {{-- Konten Teks & Tombol CTA --}}
                <div class="absolute inset-0 flex flex-col justify-center px-5 text-white sm:px-12 lg:px-16">
                    <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-amber-500/90 px-2.5 py-0.5 text-[10px] font-bold text-stone-950 shadow-xs sm:px-3 sm:py-1 sm:text-xs">
                        🍴 Makanan
                    </span>
                    <h2 class="mt-1.5 text-base font-extrabold leading-tight tracking-tight sm:mt-2.5 sm:text-3xl lg:text-4xl">
                        Aneka Makanan<br>Lezat &amp; Mengenyangkan
                    </h2>
                    <p class="mt-1.5 hidden max-w-md text-xs text-white/85 sm:block sm:text-sm">
                        Dari menu favorit hingga menu spesial, siap memanjakan selera kamu.
                    </p>
                    <span class="mt-2.5 inline-flex w-fit items-center gap-1.5 rounded-full bg-amber-400 px-3.5 py-1 text-[11px] font-bold text-stone-950 shadow-sm transition group-hover:bg-amber-300 sm:mt-4 sm:px-5 sm:py-2 sm:text-sm">
                        Pesan Sekarang →
                    </span>
                </div>
            </a>

            <!-- SLIDE 2: MINUMAN -->
            <a href="{{ route('products.index', ['category' => 'minuman']) }}"
               class="relative block aspect-[16/7] w-full flex-shrink-0 overflow-hidden sm:aspect-[1920/416]">
                <video autoplay loop muted playsinline preload="metadata"
                       class="h-full w-full object-cover object-[75%_center] sm:object-center">
                    <source src="{{ asset('videos/video_minuman_5detik.mp4') }}" type="video/mp4">
                </video>

                {{-- Overlay Gelap Lembut di Sisi Kiri --}}
                <div class="absolute inset-0 bg-gradient-to-r from-[#0B1622]/85 via-[#0B1622]/40 to-transparent"></div>

                {{-- Konten Teks & Tombol CTA --}}
                <div class="absolute inset-0 flex flex-col justify-center px-5 text-white sm:px-12 lg:px-16">
                    <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-sky-400/95 px-2.5 py-0.5 text-[10px] font-bold text-slate-950 shadow-xs sm:px-3 sm:py-1 sm:text-xs">
                        🥤 Minuman
                    </span>
                    <h2 class="mt-1.5 text-base font-extrabold leading-tight tracking-tight sm:mt-2.5 sm:text-3xl lg:text-4xl">
                        Kopi &amp; Minuman<br>Segar Setiap Hari
                    </h2>
                    <p class="mt-1.5 hidden max-w-md text-xs text-white/85 sm:block sm:text-sm">
                        Racikan kopi pilihan dan minuman dingin yang menyegarkan harimu.
                    </p>
                    <span class="mt-2.5 inline-flex w-fit items-center gap-1.5 rounded-full bg-amber-400 px-3.5 py-1 text-[11px] font-bold text-stone-950 shadow-sm transition group-hover:bg-amber-300 sm:mt-4 sm:px-5 sm:py-2 sm:text-sm">
                        Lihat Menu →
                    </span>
                </div>
            </a>

        </div>

        <!-- Panah Kiri (Hanya muncul saat kursor diarahkan ke banner) -->
        <button type="button" id="prev-slide" aria-label="Slide sebelumnya"
            class="pointer-events-none absolute left-3.5 top-1/2 z-20 hidden -translate-y-1/2 rounded-full border border-white/25 bg-black/35 p-2.5 text-white opacity-0 backdrop-blur-md transition-all duration-300 hover:bg-black/55 group-hover:pointer-events-auto group-hover:opacity-100 sm:block">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <!-- Panah Kanan (Hanya muncul saat kursor diarahkan ke banner) -->
        <button type="button" id="next-slide" aria-label="Slide berikutnya"
            class="pointer-events-none absolute right-3.5 top-1/2 z-20 hidden -translate-y-1/2 rounded-full border border-white/25 bg-black/35 p-2.5 text-white opacity-0 backdrop-blur-md transition-all duration-300 hover:bg-black/55 group-hover:pointer-events-auto group-hover:opacity-100 sm:block">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        <!-- Dots Indikator -->
        <div id="slider-dots" class="absolute bottom-2.5 left-1/2 z-20 flex -translate-x-1/2 items-center gap-1.5 rounded-full bg-black/35 px-2.5 py-1 backdrop-blur-md sm:bottom-4"></div>
    </section>

    <!-- ==================== 2. KATEGORI FAVORIT (SWIPE DI HP, 3 KOLOM DI DESKTOP) ==================== -->
    <section class="mt-7 sm:mt-10">
        <div class="mb-3 flex items-center justify-between sm:mb-4">
            <h2 class="text-base font-bold text-[#271206] sm:text-xl">Kategori Favorit</h2>
            <a href="{{ route('products.index') }}" class="text-xs font-semibold text-amber-900 hover:underline sm:text-sm">Lihat semua →</a>
        </div>

        <div class="-mx-4 flex snap-x snap-mandatory gap-3 overflow-x-auto px-4 pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden sm:mx-0 sm:grid sm:grid-cols-3 sm:gap-5 sm:overflow-visible sm:px-0 sm:pb-0">
            <a href="{{ route('products.index', ['category' => 'makanan']) }}"
               class="group relative h-32 w-[46%] flex-shrink-0 snap-start overflow-hidden rounded-2xl shadow-sm ring-1 ring-black/5 sm:h-44 sm:w-auto">
                <img src="https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&w=700&q=80"
                     alt="Makanan" class="h-full w-full object-cover transition duration-500 group-hover:scale-110">
                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/30 to-transparent"></div>
                <div class="absolute bottom-3 left-3 right-3 text-white sm:bottom-4 sm:left-4">
                    <h3 class="text-sm font-bold sm:text-lg">Makanan</h3>
                    <p class="mt-0.5 line-clamp-1 text-[11px] text-white/80 sm:text-xs">Cemilan hangat &amp; gurih</p>
                </div>
            </a>

            <a href="{{ route('products.index', ['category' => 'dessert']) }}"
               class="group relative h-32 w-[46%] flex-shrink-0 snap-start overflow-hidden rounded-2xl shadow-sm ring-1 ring-black/5 sm:h-44 sm:w-auto">
                <img src="https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?auto=format&fit=crop&w=700&q=80"
                     alt="Dessert" class="h-full w-full object-cover transition duration-500 group-hover:scale-110">
                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/30 to-transparent"></div>
                <div class="absolute bottom-3 left-3 right-3 text-white sm:bottom-4 sm:left-4">
                    <h3 class="text-sm font-bold sm:text-lg">Dessert</h3>
                    <p class="mt-0.5 line-clamp-1 text-[11px] text-white/80 sm:text-xs">Manis lembut setiap gigitan</p>
                </div>
            </a>

            <a href="{{ route('products.index', ['category' => 'minuman']) }}"
               class="group relative h-32 w-[46%] flex-shrink-0 snap-start overflow-hidden rounded-2xl shadow-sm ring-1 ring-black/5 sm:h-44 sm:w-auto">
                <img src="https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?auto=format&fit=crop&w=700&q=80"
                     alt="Minuman" class="h-full w-full object-cover transition duration-500 group-hover:scale-110">
                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/30 to-transparent"></div>
                <div class="absolute bottom-3 left-3 right-3 text-white sm:bottom-4 sm:left-4">
                    <h3 class="text-sm font-bold sm:text-lg">Minuman</h3>
                    <p class="mt-0.5 line-clamp-1 text-[11px] text-white/80 sm:text-xs">Kopi &amp; racikan segar</p>
                </div>
            </a>
        </div>
    </section>

    <!-- ==================== 3. PRODUK PILIHAN ==================== -->
    <section class="mt-10" aria-labelledby="pilihan">
        <div class="flex items-end justify-between">
            <h2 id="pilihan" class="heading-2">Produk Pilihan</h2>
            <a href="{{ route('products.index', ['sort' => 'popular']) }}" class="text-sm font-medium text-brand-700 hover:underline">Lihat semua</a>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-4">
            @forelse ($featuredProducts as $product)
                @include('partials.product-card', ['wishlisted' => in_array($product->id, $wishlistProductIds, true)])
            @empty
                <x-partials.empty-state icon="&#128230;" title="Belum ada produk tersedia" class="col-span-full" />
            @endforelse
        </div>
    </section>

    <!-- ==================== 4. PRODUK TERBARU ==================== -->
    <section class="mt-10" aria-labelledby="terbaru">
        <div class="flex items-end justify-between">
            <h2 id="terbaru" class="heading-2">Produk Terbaru</h2>
            <a href="{{ route('products.index') }}" class="text-sm font-medium text-brand-700 hover:underline">Lihat semua</a>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-4">
            @forelse ($latestProducts as $product)
                @include('partials.product-card', ['wishlisted' => in_array($product->id, $wishlistProductIds, true)])
            @empty
                <x-partials.empty-state icon="&#128230;" title="Belum ada produk" class="col-span-full" />
            @endforelse
        </div>
    </section>

    <!-- ==================== 5. ULASAN PELANGGAN ==================== -->
    @if ($reviews->isNotEmpty())
        <section class="mt-10" aria-labelledby="ulasan">
            <h2 id="ulasan" class="heading-2">Ulasan Pelanggan</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-3">
                @foreach ($reviews as $review)
                    <div class="card border border-[#E5D7C6]/80 bg-white/85 p-4 backdrop-blur-sm">
                        <p class="text-sm font-medium text-brand-500" aria-label="Rating {{ $review->rating }} dari 5">
                            @for ($i = 1; $i <= 5; $i++)
                                {{ $i <= $review->rating ? '&#9733;' : '&#9734;' }}
                            @endfor
                        </p>
                        <p class="mt-2 text-sm text-stone-700">{{ $review->comment ?? 'Tanpa komentar.' }}</p>
                        <p class="mt-3 text-xs text-brand-400">{{ $review->user?->name }} &middot; {{ $review->product?->name }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <!-- ==================== 6. INFORMASI TOKO ==================== -->
    <section class="card mt-10 border border-[#E5D7C6]/80 bg-white/85 p-6 backdrop-blur-sm sm:p-8" aria-labelledby="toko">
        <h2 id="toko" class="heading-2">Informasi Toko</h2>
        <div class="mt-4 grid gap-4 text-sm text-brand-400 sm:grid-cols-2">
            <div>
                <p class="font-medium text-brand-950">Alamat</p>
                <p class="mt-1">{{ \App\Models\Setting::get('store_address', '-') }}</p>
            </div>
            <div>
                <p class="font-medium text-brand-950">Jam Operasional</p>
                <p class="mt-1">{{ \App\Models\Setting::get('store_open_hours', '-') }}</p>
            </div>
            <div>
                <p class="font-medium text-brand-950">Telepon</p>
                <p class="mt-1">{{ \App\Models\Setting::get('store_phone', '-') }}</p>
            </div>
        </div>
    </section>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const slider = document.getElementById('hero-slider');
    if (!slider || slider.dataset.initialized === 'true') return;
    slider.dataset.initialized = 'true';

    const track = document.getElementById('slider-track');
    const slides = Array.from(track.children);
    const totalSlides = slides.length;
    const dotsContainer = document.getElementById('slider-dots');
    const prevBtn = document.getElementById('prev-slide');
    const nextBtn = document.getElementById('next-slide');

    let currentIndex = 0;
    let autoPlayTimer = null;
    let isHovered = false;
    const intervalTime = 5000; // Sinkron dengan durasi video 5 detik

    dotsContainer.innerHTML = '';
    for (let i = 0; i < totalSlides; i++) {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.ariaLabel = `Ke slide ${i + 1}`;
        dot.className = 'h-1.5 rounded-full transition-all duration-300 bg-white/45 w-1.5 hover:bg-white/80';
        dot.addEventListener('click', (e) => {
            e.preventDefault();
            goToSlide(i);
        });
        dotsContainer.appendChild(dot);
    }

    const dots = dotsContainer.children;

    function updateUI() {
        track.style.transform = `translate3d(-${currentIndex * 100}%, 0, 0)`;
        for (let i = 0; i < totalSlides; i++) {
            dots[i].classList.toggle('w-5', i === currentIndex);
            dots[i].classList.toggle('bg-white', i === currentIndex);
            dots[i].classList.toggle('w-1.5', i !== currentIndex);
            dots[i].classList.toggle('bg-white/45', i !== currentIndex);

            const video = slides[i].querySelector('video');
            if (video) {
                if (i === currentIndex) {
                    video.currentTime = 0;
                    video.play().catch(() => {});
                } else {
                    video.pause();
                }
            }
        }
    }

    function stopAutoPlay() {
        if (autoPlayTimer) {
            clearTimeout(autoPlayTimer);
            autoPlayTimer = null;
        }
    }

    function scheduleNextSlide() {
        stopAutoPlay();
        if (totalSlides <= 1 || isHovered || document.hidden) return;
        autoPlayTimer = setTimeout(() => {
            currentIndex = (currentIndex + 1) % totalSlides;
            updateUI();
            scheduleNextSlide();
        }, intervalTime);
    }

    function goToSlide(index) {
        currentIndex = (index + totalSlides) % totalSlides;
        updateUI();
        scheduleNextSlide();
    }

    if (prevBtn) prevBtn.addEventListener('click', (e) => { e.preventDefault(); goToSlide(currentIndex - 1); });
    if (nextBtn) nextBtn.addEventListener('click', (e) => { e.preventDefault(); goToSlide(currentIndex + 1); });

    slider.addEventListener('mouseenter', () => { isHovered = true; stopAutoPlay(); });
    slider.addEventListener('mouseleave', () => { isHovered = false; scheduleNextSlide(); });
    document.addEventListener('visibilitychange', () => document.hidden ? stopAutoPlay() : (!isHovered && scheduleNextSlide()));

    // Dukungan Swipe Jari di HP
    let touchStartX = 0;
    slider.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
        stopAutoPlay();
    }, { passive: true });

    slider.addEventListener('touchend', (e) => {
        const diff = touchStartX - e.changedTouches[0].screenX;
        if (Math.abs(diff) > 40) {
            goToSlide(diff > 0 ? currentIndex + 1 : currentIndex - 1);
        } else {
            scheduleNextSlide();
        }
    }, { passive: true });

    updateUI();
    scheduleNextSlide();
});
</script>
@endsection