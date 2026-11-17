<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Beranda') — {{ config('app.name', 'Brew & Bliss') }}</title>

    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>&#9749;</text></svg>">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="flex min-h-screen flex-col bg-brand-50 pb-16 text-brand-950 antialiased md:pb-0">

    @php
        $cartCount = auth()->user()?->cartItems()->sum('quantity') ?? 0;
        $wishlistCount = auth()->user()?->wishlists()->count() ?? 0;
        $storeName = \App\Models\Setting::get('store_name', 'Brew & Bliss');
    @endphp

    <header class="sticky top-0 z-40 border-b border-brand-100 bg-white/95 backdrop-blur">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex h-14 items-center justify-between gap-3">
                <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2">
                    <span class="grid h-9 w-9 place-items-center rounded-full bg-brand-700 text-lg text-white" aria-hidden="true">&#9749;</span>
                    <span class="text-lg font-semibold leading-none text-brand-900">{{ $storeName }}</span>
                </a>

                <nav class="hidden items-center gap-1 md:flex" aria-label="Navigasi utama">
                    <a href="{{ route('home') }}" class="rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('home') ? 'bg-brand-50 text-brand-900' : 'text-brand-400 hover:bg-brand-50 hover:text-brand-900' }}">Beranda</a>
                    <a href="{{ route('products.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('products.*') ? 'bg-brand-50 text-brand-900' : 'text-brand-400 hover:bg-brand-50 hover:text-brand-900' }}">Katalog</a>
                    <a href="{{ route('cart.index') }}" class="relative rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('cart.index') ? 'bg-brand-50 text-brand-900' : 'text-brand-400 hover:bg-brand-50 hover:text-brand-900' }}">Keranjang
                        @if ($cartCount > 0)
                            <span class="absolute -top-1 -right-1 grid h-5 min-w-5 place-items-center rounded-full bg-brand-500 px-1 text-[11px] font-semibold text-white">{{ $cartCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('wishlist.index') }}" class="relative rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('wishlist.*') ? 'bg-brand-50 text-brand-900' : 'text-brand-400 hover:bg-brand-50 hover:text-brand-900' }}">Wishlist
                        @if ($wishlistCount > 0)
                            <span class="absolute -top-1 -right-1 grid h-5 min-w-5 place-items-center rounded-full bg-brand-500 px-1 text-[11px] font-semibold text-white">{{ $wishlistCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('orders.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('orders.*') ? 'bg-brand-50 text-brand-900' : 'text-brand-400 hover:bg-brand-50 hover:text-brand-900' }}">Pesanan</a>
                    <a href="{{ route('chat.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('chat.index') ? 'bg-brand-50 text-brand-900' : 'text-brand-400 hover:bg-brand-50 hover:text-brand-900' }}">Bantuan</a>
                    <a href="{{ route('feedback.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('feedback.index') ? 'bg-brand-50 text-brand-900' : 'text-brand-400 hover:bg-brand-50 hover:text-brand-900' }}">Kritik &amp; Saran</a>
                </nav>

                <div class="flex items-center gap-2">
                    @auth
                        <a href="{{ route('profile.index') }}" class="hidden text-sm font-medium text-brand-700 hover:text-brand-900 hover:underline md:inline" title="Profil saya">
                            {{ auth()->user()->name }}
                        </a>
                        <a href="{{ route('profile.index') }}" class="relative grid h-9 w-9 place-items-center rounded-lg text-brand-400 hover:bg-brand-50 hover:text-brand-900 md:hidden" aria-label="Profil saya">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        </a>
                        <form method="POST" action="{{ route('auth.logout') }}" class="hidden md:block">
                            @csrf
                            <button type="submit" class="btn btn-ghost !min-h-0 px-3 py-2">Keluar</button>
                        </form>
                    @else
                        <a href="{{ route('auth.login') }}" class="btn btn-primary !min-h-0 hidden px-3.5 py-2 sm:inline-flex">masuk</a>
                    @endauth
                </div>
            </div>

            <form method="GET" action="{{ route('products.index') }}" role="search" class="pb-3 md:hidden">
                <div class="relative">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari produk..." aria-label="Cari produk"
                           class="input !mt-0 bg-brand-50 pl-10">
                    <svg class="absolute left-3 top-3 h-4.5 w-4.5 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                </div>
            </form>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-6 sm:px-6 sm:py-8">
       

        @include('components.partials.form-errors')

        @yield('content')
    </main>

    <footer class="mt-auto border-t border-brand-100 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
            <div class="grid gap-6 sm:grid-cols-3">
                <div>
                    <p class="font-semibold text-brand-900">{{ $storeName }}</p>
                    <p class="mt-2 text-sm text-brand-400">{{ \App\Models\Setting::get('store_description', 'Menghadirkan kehangatan di setiap sajian. Kami meracik berbagai pilihan minuman segar, dessert manis, dan camilan buatan rumah dengan bahan-bahan pilihan untuk menemani hari Anda.') }}</p>
                </div>
                <div class="text-sm text-brand-400">
                    <p class="font-medium text-brand-950">Kontak &amp; Jam Buka</p>
                    <p class="mt-2">{{ \App\Models\Setting::get('store_address', '') }}</p>
                    <p class="mt-1">{{ \App\Models\Setting::get('store_open_hours', '') }}</p>
                    <p class="mt-1">{{ \App\Models\Setting::get('store_phone', '') }}</p>
                </div>
                <div class="text-sm text-brand-400">
                    <p class="font-medium text-brand-950">Jelajahi</p>
                    <p class="mt-2"><a href="{{ route('home') }}" class="hover:text-brand-900">Beranda</a></p>
                    <p class="mt-1"><a href="{{ route('products.index') }}" class="hover:text-brand-900">Katalog Produk</a></p>
                    <p class="mt-1"><a href="{{ route('cart.index') }}" class="hover:text-brand-900">Keranjang</a></p>
                    <p class="mt-1"><a href="{{ route('faq') }}" class="hover:text-brand-900">FAQ</a></p>

                    <p class="mt-1"><a href="{{ route('chat.index') }}" class="hover:text-brand-900">Bantuan</a></p>
                </div>
            </div>
            <p class="mt-8 border-t border-brand-50 pt-4 text-center text-xs text-brand-400">&copy; {{ now()->year }} {{ $storeName }}. Semua hak dilindungi.</p>
        </div>
    </footer>

    <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-brand-100 bg-white md:hidden" aria-label="Navigasi mobile">
        <div class="grid grid-cols-5">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-0.5 py-2 text-[11px] font-medium {{ request()->routeIs('home') ? 'text-brand-900' : 'text-brand-400' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75a1.125 1.125 0 0 0 1.125-1.125v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125a1.125 1.125 0 0 0 1.125-1.125V9.75" /></svg>
                Beranda
            </a>
            <a href="{{ route('products.index') }}" class="flex flex-col items-center gap-0.5 py-2 text-[11px] font-medium {{ request()->routeIs('products.*') ? 'text-brand-900' : 'text-brand-400' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 19.941 0M4.5 15.75h15M4.5 11.25h15m-16.5-7.5h18L19.5 11.25h-15L1.5 3.75Z" /></svg>
                Katalog
            </a>
            <a href="{{ route('cart.index') }}" class="relative flex flex-col items-center gap-0.5 py-2 text-[11px] font-medium {{ request()->routeIs('cart.index') ? 'text-brand-900' : 'text-brand-400' }}">
                <span class="relative">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm2.028-9.47-.484 9.24" /></svg>
                    @if ($cartCount > 0)
                        <span class="absolute -top-1.5 -right-2 grid h-4 min-w-4 place-items-center rounded-full bg-brand-500 px-1 text-[10px] font-semibold text-white">{{ $cartCount }}</span>
                    @endif
                </span>
                Keranjang
            </a>
            <a href="{{ route('wishlist.index') }}" class="relative flex flex-col items-center gap-0.5 py-2 text-[11px] font-medium {{ request()->routeIs('wishlist.*') ? 'text-brand-900' : 'text-brand-400' }}">
                <span class="relative">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                    @if ($wishlistCount > 0)
                        <span class="absolute -top-1.5 -right-2 grid h-4 min-w-4 place-items-center rounded-full bg-brand-500 px-1 text-[10px] font-semibold text-white">{{ $wishlistCount }}</span>
                    @endif
                </span>
                Wishlist
            </a>
            <a href="{{ route('orders.index') }}" class="flex flex-col items-center gap-0.5 py-2 text-[11px] font-medium {{ request()->routeIs('orders.*') ? 'text-brand-900' : 'text-brand-400' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M9 8h6M5.25 21h13.5A1.75 1.75 0 0 0 20.5 19.25V4.75A1.75 1.75 0 0 0 18.75 3H5.25A1.75 1.75 0 0 0 3.5 4.75v14.5c0 .966.784 1.75 1.75 1.75Z" /></svg>
                Pesanan
            </a>
        </div>
    </nav>

    @php
        $whatsappNumber = trim((string) \App\Models\Setting::get('store_whatsapp', ''));
    @endphp

    @if ($whatsappNumber !== '')
        <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener noreferrer"
           class="fixed right-4 bottom-20 z-40 flex h-13 w-13 items-center justify-center rounded-full bg-[#25D366] text-white shadow-lg shadow-stone-900/20 transition hover:scale-105 hover:bg-[#1EBE5A] md:right-6 md:bottom-6"
           aria-label="Hubungi kami via WhatsApp">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
        </a>
    @endif

</body>
{{-- ==================== FLOATING TOAST BUBBLE (AUTO-DISMISS 4.5 DETIK) ==================== --}}
@php
    $toastMessage = session('success') ?? session('status') ?? session('error');
    $toastType = session('error') ? 'error' : 'success';
@endphp

@if ($toastMessage)
    <div id="brew-toast"
         role="status"
         aria-live="polite"
         class="fixed left-4 right-4 top-20 z-50 mx-auto max-w-sm translate-y-0 opacity-100 transition-all duration-500 ease-out sm:left-auto sm:right-5 sm:mx-0 sm:w-full">
        <div class="relative overflow-hidden rounded-2xl border {{ $toastType === 'error' ? 'border-rose-200 bg-white/95' : 'border-[#E2D2BF] bg-white/95' }} px-4 py-3.5 shadow-xl shadow-stone-900/10 backdrop-blur-md">
            <div class="flex items-center gap-3">
                {{-- Ikon Bubble --}}
                @if ($toastType === 'error')
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </span>
                @else
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </span>
                @endif

                {{-- Isi Pesan --}}
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold uppercase tracking-wider {{ $toastType === 'error' ? 'text-rose-600' : 'text-[#7A5236]' }}">
                        {{ $toastType === 'error' ? 'Perhatian' : 'Berhasil' }}
                    </p>
                    <p class="mt-0.5 text-sm font-medium leading-snug text-[#271206]">
                        {{ $toastMessage }}
                    </p>
                </div>

                {{-- Tombol Tutup Manual --}}
                <button type="button"
                        id="brew-toast-close"
                        aria-label="Tutup notifikasi"
                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-stone-400 transition hover:bg-stone-100 hover:text-stone-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Bar Timer 4.5 Detik --}}
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-stone-100">
                <div id="brew-toast-progress"
                     class="h-full w-full {{ $toastType === 'error' ? 'bg-rose-500' : 'bg-[#6B4226]' }}"
                     style="transition: width 3000ms linear;"></div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const toast = document.getElementById('brew-toast');
        const progress = document.getElementById('brew-toast-progress');
        const closeBtn = document.getElementById('brew-toast-close');
        if (!toast) return;

        const dismissToast = () => {
            toast.classList.add('opacity-0', '-translate-y-2', 'pointer-events-none');
            setTimeout(() => toast.remove(), 500);
        };

        // Jalankan animasi bar waktu 4.5 detik
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                if (progress) progress.style.width = '0%';
            });
        });

        const autoCloseTimer = setTimeout(dismissToast, 4500);

        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                clearTimeout(autoCloseTimer);
                dismissToast();
            });
        }
    });
    </script>
@endif
</html>
