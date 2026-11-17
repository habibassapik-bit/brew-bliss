@extends('layouts.app')

@section('title', 'Daftar')

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
<div class="relative z-10 mx-auto max-w-md py-4 sm:py-8">
    <div class="card overflow-hidden rounded-3xl border border-[#E5D7C6]/85 bg-white/90 p-6 shadow-md backdrop-blur-md sm:p-8">
        {{-- Header Kartu Daftar --}}
        <div class="text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#F5EDE3] text-[#6B4226] shadow-inner">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                </svg>
            </div>
            <h1 class="heading-2 mt-3.5 text-xl sm:text-2xl">Daftar Akun</h1>
            <p class="mt-1 text-sm text-brand-400">Buat akun pelanggan Brew &amp; Bliss.</p>
        </div>

        <form method="POST" action="{{ route('auth.register.store') }}" class="mt-6 space-y-4">
            @csrf

            {{-- Nama Lengkap --}}
            <div>
                <label for="name" class="label">Nama Lengkap</label>
                <div class="relative mt-1">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-brand-400"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                    <input id="name"
                           name="name"
                           type="text"
                           value="{{ old('name') }}"
                           placeholder="Masukkan nama lengkap"
                           required
                           autofocus
                           autocomplete="name"
                           class="input !mt-0 bg-white/95 !pl-10">
                </div>
                @error('name') <p class="error-text">{{ $message }}</p> @enderror
            </div>

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
                           autocomplete="email"
                           class="input !mt-0 bg-white/95 !pl-10">
                </div>
                @error('email') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            {{-- Nomor Telepon --}}
            <div>
                <label for="phone" class="label">Nomor Telepon</label>
                <div class="relative mt-1">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-brand-400"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                    </svg>
                    <input id="phone"
                           name="phone"
                           type="tel"
                           value="{{ old('phone') }}"
                           required
                           autocomplete="tel"
                           placeholder="08xxxxxxxxxx"
                           class="input !mt-0 bg-white/95 !pl-10">
                </div>
                @error('phone') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            {{-- Kata Sandi --}}
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
                           autocomplete="new-password"
                           class="input !mt-0 bg-white/95 !pl-10 !pr-10">
                    <button type="button"
                            data-toggle-target="password"
                            aria-label="Tampilkan kata sandi"
                            class="toggle-password-btn absolute right-3 top-1/2 -translate-y-1/2 text-brand-400 transition hover:text-brand-700">
                        <svg class="eye-open h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <svg class="eye-closed hidden h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                @error('password') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            {{-- Konfirmasi Kata Sandi --}}
            <div>
                <label for="password_confirmation" class="label">Konfirmasi Kata Sandi</label>
                <div class="relative mt-1">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-brand-400"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                    <input id="password_confirmation"
                           name="password_confirmation"
                           type="password"
                           placeholder="••••••••"
                           required
                           autocomplete="new-password"
                           class="input !mt-0 bg-white/95 !pl-10 !pr-10">
                    <button type="button"
                            data-toggle-target="password_confirmation"
                            aria-label="Tampilkan konfirmasi kata sandi"
                            class="toggle-password-btn absolute right-3 top-1/2 -translate-y-1/2 text-brand-400 transition hover:text-brand-700">
                        <svg class="eye-open h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <svg class="eye-closed hidden h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="pt-1">
                <button type="submit" class="btn btn-primary w-full py-2.5 text-sm font-semibold shadow-sm">
                    Daftar
                </button>
            </div>
        </form>

        <div class="mt-6 border-t border-stone-100 pt-5 text-center">
            <p class="text-sm text-brand-400">
                Sudah punya akun?
                <a href="{{ route('auth.login') }}" class="font-semibold text-brand-700 hover:underline">Masuk</a>
            </p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.toggle-password-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-toggle-target');
            const input = document.getElementById(targetId);
            if (!input) return;

            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';

            const eyeOpen = btn.querySelector('.eye-open');
            const eyeClosed = btn.querySelector('.eye-closed');
            if (eyeOpen) eyeOpen.classList.toggle('hidden', isHidden);
            if (eyeClosed) eyeClosed.classList.toggle('hidden', !isHidden);
        });
    });
});
</script>
@endsection