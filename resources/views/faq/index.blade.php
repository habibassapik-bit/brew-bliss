@extends('layouts.app')

@section('title', 'FAQ')

@section('content')
    <h1 class="heading-1">Pertanyaan yang Sering Diajukan</h1>
    <p class="mt-1 text-sm text-brand-400">Informasi umum seputar pemesanan, pembayaran, dan layanan Brew &amp; Bliss.</p>

    @if ($faqs->isEmpty())
        <x-partials.empty-state
            icon="&#10067;"
            title="Belum ada pertanyaan"
            description="Halaman ini akan berisi pertanyaan yang sering diajukan. Sementara itu, hubungi kami lewat halaman Bantuan."
            actionHref="{{ route('chat.index') }}"
            actionLabel="Buka Bantuan"
            class="mt-6" />
    @else
        <div class="card mt-6 divide-y divide-brand-50">
            @foreach ($faqs as $faq)
                <details class="group px-4 py-3 sm:px-5" {{ $loop->first ? 'open' : '' }}>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 py-1 text-sm font-semibold text-brand-950 sm:text-base">
                        {{ $faq['question'] }}
                        <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-brand-50 text-lg font-bold text-brand-700 transition-transform duration-200 group-open:rotate-45" aria-hidden="true">+</span>
                    </summary>
                    <p class="mt-2 pr-8 text-sm leading-relaxed text-brand-400">{{ $faq['answer'] }}</p>
                </details>
            @endforeach
        </div>

        <div class="card mt-6 p-5">
            <h2 class="heading-3">Masih butuh bantuan?</h2>
            <p class="mt-1 text-sm text-brand-400">Tim kami siap membantu lewat halaman Bantuan.</p>
            <a href="{{ route('chat.index') }}" class="btn btn-secondary mt-3">Hubungi Customer Service</a>
        </div>
    @endif
@endsection
