<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') — Admin {{ config('app.name', 'Brew & Bliss') }}</title>

    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>&#9749;</text></svg>">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-stone-100 text-brand-950 antialiased">

    @php
        $storeName = config('app.name', 'Brew & Bliss');
    @endphp

    <div id="admin-drawer-overlay" class="fixed inset-0 z-40 hidden bg-brand-950/50 md:hidden" aria-hidden="true"></div>

    <div class="mx-auto flex min-h-screen w-full max-w-7xl">
        <aside id="admin-drawer"
               class="fixed inset-y-0 left-0 z-50 w-64 -translate-x-full border-r border-brand-100 bg-white transition-transform duration-200 md:static md:translate-x-0"
               aria-label="Sidebar admin">
            <div class="flex items-center justify-between gap-3 px-4 py-4">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                    <span class="grid h-9 w-9 place-items-center rounded-full bg-brand-700 text-lg text-white" aria-hidden="true">&#9749;</span>
                    <span class="leading-none">
                        <span class="block font-semibold text-brand-900">{{ $storeName }}</span>
                        <span class="block text-xs font-medium uppercase tracking-wide text-brand-400">Panel Admin</span>
                    </span>
                </a>
                <button type="button" data-drawer-close class="grid h-9 w-9 place-items-center rounded-lg text-brand-400 hover:bg-brand-50 md:hidden" aria-label="Tutup menu">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <nav class="px-3 pb-4" aria-label="Navigasi admin">
                @php
                    $isActive = fn (string $route): bool => request()->routeIs($route);
                @endphp
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium {{ $isActive('admin.dashboard') ? 'bg-brand-50 text-brand-900' : 'text-stone-500 hover:bg-brand-50 hover:text-brand-900' }}" @if ($isActive('admin.dashboard')) aria-current="page" @endif>
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                    Dashboard
                </a>

                <p class="mt-4 px-3 text-xs font-medium uppercase tracking-wide text-brand-400">Katalog</p>
                <a href="{{ route('admin.products.index') }}" class="mt-1 flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium {{ $isActive('admin.products.*') ? 'bg-brand-50 text-brand-900' : 'text-stone-500 hover:bg-brand-50 hover:text-brand-900' }}" @if ($isActive('admin.products.*')) aria-current="page" @endif>
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                    Produk
                </a>
                <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium {{ $isActive('admin.categories.*') ? 'bg-brand-50 text-brand-900' : 'text-stone-500 hover:bg-brand-50 hover:text-brand-900' }}" @if ($isActive('admin.categories.*')) aria-current="page" @endif>
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.25-3h-3a3 3 0 0 0 0 6h3a3 3 0 0 0 0-6ZM6 3.75h12A2.25 2.25 0 0 1 20.25 6v12A2.25 2.25 0 0 1 18 20.25H6A2.25 2.25 0 0 1 3.75 18V6A2.25 2.25 0 0 1 6 3.75Z" /></svg>
                    Kategori
                </a>

                <p class="mt-4 px-3 text-xs font-medium uppercase tracking-wide text-brand-400">Pesanan</p>
                <a href="{{ route('admin.orders.index') }}" class="mt-1 flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium {{ $isActive('admin.orders.*') ? 'bg-brand-50 text-brand-900' : 'text-stone-500 hover:bg-brand-50 hover:text-brand-900' }}" @if ($isActive('admin.orders.*')) aria-current="page" @endif>
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M9 8h6M5.25 21h13.5A1.75 1.75 0 0 0 20.5 19.25V4.75A1.75 1.75 0 0 0 18.75 3H5.25A1.75 1.75 0 0 0 3.5 4.75v14.5c0 .966.784 1.75 1.75 1.75Z" /></svg>
                    Pesanan
                </a>
                <a href="{{ route('admin.payments.index') }}" class="relative flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium {{ $isActive('admin.payments.*') ? 'bg-brand-50 text-brand-900' : 'text-stone-500 hover:bg-brand-50 hover:text-brand-900' }}" @if ($isActive('admin.payments.*')) aria-current="page" @endif>
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" /></svg>
                    Verifikasi Pembayaran
                    @if (($pendingPaymentsCount = \App\Models\Payment::query()->where('status', \App\Enums\PaymentStatus::PendingVerification)->count()) > 0)
                        <span class="ml-auto grid h-5 min-w-5 place-items-center rounded-full bg-brand-500 px-1 text-[11px] font-semibold text-white">{{ $pendingPaymentsCount }}</span>
                    @endif
                </a>

                <p class="mt-4 px-3 text-xs font-medium uppercase tracking-wide text-brand-400">Pelanggan &amp; Layanan</p>
                <a href="{{ route('admin.customers.index') }}" class="mt-1 flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium {{ $isActive('admin.customers.*') ? 'bg-brand-50 text-brand-900' : 'text-stone-500 hover:bg-brand-50 hover:text-brand-900' }}" @if ($isActive('admin.customers.*')) aria-current="page" @endif>
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.76M15 19.128c.056 0 .112-.002.168-.003M12.75 12.75a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9Zm-6 6.75a8.25 8.25 0 1 1 16.5 0" /></svg>
                    Data Pelanggan
                </a>
                <a href="{{ route('admin.feedbacks.index') }}" class="relative flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium {{ $isActive('admin.feedbacks.*') ? 'bg-brand-50 text-brand-900' : 'text-stone-500 hover:bg-brand-50 hover:text-brand-900' }}" @if ($isActive('admin.feedbacks.*')) aria-current="page" @endif>
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" /></svg>
                    Kritik &amp; Saran
                    @if (($newFeedbackCount = \App\Models\Feedback::query()->where('status', \App\Enums\FeedbackStatus::New->value)->count()) > 0)
                        <span class="ml-auto grid h-5 min-w-5 place-items-center rounded-full bg-brand-500 px-1 text-[11px] font-semibold text-white">{{ $newFeedbackCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.conversations.index') }}" class="relative flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium {{ $isActive('admin.conversations.*') ? 'bg-brand-50 text-brand-900' : 'text-stone-500 hover:bg-brand-50 hover:text-brand-900' }}" @if ($isActive('admin.conversations.*')) aria-current="page" @endif>
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                    Customer Service
                </a>
                <a href="{{ route('admin.vouchers.index') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium {{ $isActive('admin.vouchers.*') ? 'bg-brand-50 text-brand-900' : 'text-stone-500 hover:bg-brand-50 hover:text-brand-900' }}" @if ($isActive('admin.vouchers.*')) aria-current="page" @endif>
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H4.5a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                    Voucher &amp; Promo
                </a>

                <p class="mt-4 px-3 text-xs font-medium uppercase tracking-wide text-brand-400">Operasional</p>
                <a href="{{ route('admin.customers.index') }}" class="mt-1 flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium {{ $isActive('admin.customers.*') ? 'bg-brand-50 text-brand-900' : 'text-stone-500 hover:bg-brand-50 hover:text-brand-900' }}" @if ($isActive('admin.customers.*')) aria-current="page" @endif>
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                    Pelanggan
                </a>
                <a href="{{ route('admin.feedbacks.index') }}" class="relative flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium {{ $isActive('admin.feedbacks.*') ? 'bg-brand-50 text-brand-900' : 'text-stone-500 hover:bg-brand-50 hover:text-brand-900' }}" @if ($isActive('admin.feedbacks.*')) aria-current="page" @endif>
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.59-2.095a6.005 6.005 0 1 0-7.68 0c.932.272 1.59 1.112 1.59 2.095v.192" /></svg>
                    Kritik &amp; Saran
                    @if (($newFeedbacksCount = \App\Models\Feedback::query()->where('status', \App\Enums\FeedbackStatus::New->value)->count()) > 0)
                        <span class="ml-auto grid h-5 min-w-5 place-items-center rounded-full bg-brand-500 px-1 text-[11px] font-semibold text-white">{{ $newFeedbacksCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.conversations.index') }}" class="relative flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium {{ $isActive('admin.conversations.*') ? 'bg-brand-50 text-brand-900' : 'text-stone-500 hover:bg-brand-50 hover:text-brand-900' }}" @if ($isActive('admin.conversations.*')) aria-current="page" @endif>
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-7.363-.415c-2.408 0-4.812.085-7.207.252a2.14 2.14 0 0 0-.977.264M5.25 8.511c-.884.284-1.5 1.128-1.5 2.097v4.286c0 1.136.847 2.1 1.98 2.193.34.027.68.052 1.02.072v3.091l3-3c1.354 0 2.694-.055 4.02-.163a2.115 2.115 0 0 0 .825-.242M5.25 8.511a2.13 2.13 0 0 1 .476-.095 48.64 48.64 0 0 1 7.363-.415c2.408 0 4.812.085 7.207.252" /></svg>
                    Customer Service
                </a>

                <p class="mt-4 px-3 text-xs font-medium uppercase tracking-wide text-brand-400">Promo</p>
                <a href="{{ route('admin.vouchers.index') }}" class="mt-1 flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium {{ $isActive('admin.vouchers.*') ? 'bg-brand-50 text-brand-900' : 'text-stone-500 hover:bg-brand-50 hover:text-brand-900' }}" @if ($isActive('admin.vouchers.*')) aria-current="page" @endif>
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" /></svg>
                    Voucher &amp; Promo
                </a>

                <p class="mt-4 px-3 text-xs font-medium uppercase tracking-wide text-brand-400">Laporan &amp; Aktivitas</p>
                <a href="{{ route('admin.reports.index') }}" class="mt-1 flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium {{ $isActive('admin.reports.*') ? 'bg-brand-50 text-brand-900' : 'text-stone-500 hover:bg-brand-50 hover:text-brand-900' }}" @if ($isActive('admin.reports.*')) aria-current="page" @endif>
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                    Laporan Penjualan
                </a>
                <a href="{{ route('admin.costs.index') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium {{ $isActive('admin.costs.*') ? 'bg-brand-50 text-brand-900' : 'text-stone-500 hover:bg-brand-50 hover:text-brand-900' }}" @if ($isActive('admin.costs.*')) aria-current="page" @endif>
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" /></svg>
                    Biaya Operasional
                </a>
                <a href="{{ route('admin.activity-logs.index') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium {{ $isActive('admin.activity-logs.*') ? 'bg-brand-50 text-brand-900' : 'text-stone-500 hover:bg-brand-50 hover:text-brand-900' }}" @if ($isActive('admin.activity-logs.*')) aria-current="page" @endif>
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                    Log Aktivitas
                </a>

                <p class="mt-4 px-3 text-xs font-medium uppercase tracking-wide text-brand-400">Pengaturan</p>
                <a href="{{ route('admin.settings.edit') }}" class="mt-1 flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium {{ $isActive('admin.settings.*') ? 'bg-brand-50 text-brand-900' : 'text-stone-500 hover:bg-brand-50 hover:text-brand-900' }}" @if ($isActive('admin.settings.*')) aria-current="page" @endif>
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.99l1.005.828c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a7.723 7.723 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                    Pengaturan Toko
                </a>

                <div class="mt-6 border-t border-brand-50 px-3 pt-4">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium text-brand-400 hover:bg-brand-50 hover:text-brand-900">
                        Lihat Toko
                    </a>
                    <form method="POST" action="{{ route('auth.logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-red-600 hover:bg-red-50">
                            Keluar
                        </button>
                    </form>
                </div>
            </nav>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-30 flex items-center justify-between gap-3 border-b border-brand-100 bg-white/95 px-4 py-3 backdrop-blur sm:px-6">
                <div class="flex items-center gap-2">
                    <button type="button" data-drawer-open class="grid h-10 w-10 place-items-center rounded-lg text-brand-400 hover:bg-brand-50 md:hidden" aria-label="Buka menu admin" aria-controls="admin-drawer" aria-expanded="false">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                    </button>
                    <p class="min-w-0 truncate text-sm text-brand-400">
                        Masuk sebagai
                        <span class="font-semibold text-brand-950">{{ auth()->user()?->name }}</span>
                        <span class="badge badge-warning ml-1">
                            <span class="badge-dot"></span> Super Admin
                        </span>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('home') }}" class="btn btn-ghost !min-h-0 hidden px-3 py-2 sm:inline-flex">Lihat Toko</a>
                    <form method="POST" action="{{ route('auth.logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-danger !min-h-0 px-3 py-2">Keluar</button>
                    </form>
                </div>
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6">
                @if (session('status'))
                    <x-partials.status-banner type="success" class="mb-4">{{ session('status') }}</x-partials.status-banner>
                @endif

                @include('components.partials.form-errors')

                @yield('content')
            </main>
        </div>
    </div>

</body>
</html>
