@extends('layouts.app')

@section('title', 'Masuk')

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
        <svg class="absolute left-[4%] top-[34%] h-14 w-14 -rotate-12 sm:h-16 sm:w-16" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M32 24 C28 12, 36 12, 32 24 Z" />
            <path d="M32 24 C22 14, 14 22, 25 29" />
            <path d="M25 29 C12 29, 12 40, 25 37" />
            <path d="M25 37 C17 48, 27 54, 32 41" />
            <path d="M32 41 C37 54, 47 48, 39 37" />
            <path d="M39 37 C52 40, 52 29, 39 29" />
            <path d="M39 29 C50 22, 42 14, 32 24" />
            <circle cx="32" cy="32" r="3.5" />
        </svg>

        {{-- Daun Kiri Atas --}}
        <svg class="absolute left-[18%] top-[20%] hidden h-12 w-12 rotate-12 sm:block" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
            <path d="M14 50 C16 26, 32 14, 52 12 C50 32, 38 48, 14 50 Z" />
            <path d="M14 50 L48 16" />
            <path d="M26 38 L24 27 M34 30 L32 20 M28 36 L39 38 M36 28 L45 30" />
        </svg>

        {{-- Biji Kopi Kanan Atas --}}
        <svg class="absolute right-[20%] top-[24%] h-11 w-11 rotate-12 sm:h-12 sm:w-12" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">
            <ellipse cx="24" cy="24" rx="11" ry="16" transform="rotate(25 24 24)" />
            <path d="M30 10 C22 18, 26 30, 18 38" />
        </svg>

        {{-- Bunga & Sulur Kanan Tengah --}}
        <svg class="absolute right-[4%] top-[46%] h-14 w-14 rotate-12 sm:h-16 sm:w-16" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
            <path d="M32 24 C22 14, 14 22, 25 29 C12 29, 12 40, 25 37 C17 48, 27 54, 32 41 C37 54, 47 48, 39 37 C52 40, 52 29, 39 29 C50 22, 42 14, 32 24 Z" />
            <circle cx="32" cy="32" r="3" />
            <path d="M46 46 C54 52, 52 60, 42 60" />
        </svg>

        {{-- Biji Kopi Bawah Kiri --}}
        <svg class="absolute bottom-[18%] left-[16%] h-11 w-11 rotate-45" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">
            <ellipse cx="24" cy="24" rx="11" ry="16" transform="rotate(25 24 24)" />
            <path d="M30 10 C22 18, 26 30, 18 38" />
        </svg>

        {{-- Daun Bawah Kanan --}}
        <svg class="absolute bottom-[16%] right-[18%] hidden h-12 w-12 -rotate-45 sm:block" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
            <path d="M14 50 C16 26, 32 14, 52 12 C50 32, 38 48, 14 50 Z" />
            <path d="M14 50 L48 16" />
            <path d="M26 38 L24 27 M34 30 L32 20 M28 36 L39 38" />
        </svg>
    </div>
</div>

{{-- ==================== KONTEN UTAMA ==================== --}}
<div class="relative z-10 mx-auto max-w-md py-4 sm:py-10">
    <div class="card overflow-hidden rounded-3xl border border-[#E5D7C6]/85 bg-white/90 p-6 shadow-md backdrop-blur-md sm:p-8">
        {{-- Header Kartu Login --}}
        <div class="text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#F5EDE3] text-[#6B4226] shadow-inner">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 8h1a4 4 0 010 8h-1M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8zm4-4h10" />
                </svg>
            </div>
            <h1 class="heading-2 mt-3.5 text-xl sm:text-2xl">Selamat Datang Kembali</h1>
            <p class="mt-1 text-sm text-brand-400">Masuk untuk memesan dan mengelola keranjangmu.</p>
        </div>

        <form method="POST" action="{{ route('auth.login.store') }}" class="mt-6 space-y-4">
            @csrf

            {{-- Email --}}
            <div>
                <label for="email" class="label">Email</label>
                <div class="relative mt-1">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-brand-400"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                    <input id="email"
                           name="email"
                           type="email"
                           value="{{ old('email') }}"
                           placeholder="nama@email.com"
                           required
                           autofocus
                           autocomplete="email"
                           class="input !mt-0 bg-white/95 !pl-10">
                </div>
                @error('email') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            {{-- Kata Sandi + Toggle Intip Password --}}
            <div>
                <label for="password" class="label">Kata Sandi</label>
                <div class="relative mt-1">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-brand-400"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                    <input id="password"
                           name="password"
                           type="password"
                           placeholder="••••••••"
                           required
                           autocomplete="current-password"
                           class="input !mt-0 bg-white/95 !pl-10 !pr-10">
                    <button type="button"
                            id="toggle-password"
                            aria-label="Tampilkan kata sandi"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-brand-400 transition hover:text-brand-700">
                        <svg id="eye-open" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <svg id="eye-closed" class="hidden h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                @error('password') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            {{-- Ingat Saya --}}
            <div class="flex items-center justify-between pt-0.5">
                <label class="flex cursor-pointer items-center gap-2 text-sm text-brand-400 select-none">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-stone-300 accent-brand-700">
                    <span>Ingat saya</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary w-full py-2.5 text-sm font-semibold shadow-sm">
                Masuk
            </button>
        </form>

        <div class="mt-6 border-t border-stone-100 pt-5 text-center">
            <p class="text-sm text-brand-400">
                Belum punya akun?
                <a href="{{ route('auth.register') }}" class="font-semibold text-brand-700 hover:underline">Daftar sekarang</a>
            </p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('password');
    const eyeOpen = document.getElementById('eye-open');
    const eyeClosed = document.getElementById('eye-closed');

    if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener('click', () => {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            eyeOpen.classList.toggle('hidden', isHidden);
            eyeClosed.classList.toggle('hidden', !isHidden);
        });
    }
});
</script>
@endsection