@extends('layouts.app')

@section('title', 'Kritik & Saran')

@section('content')
    <h1 class="heading-1">Kritik &amp; Saran</h1>
    <p class="mt-1 text-sm text-brand-400">Masukanmu membantu kami berkembang. Riwayat dan balasan admin tampil di bawah.</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="card p-5" aria-labelledby="form-feedback">
            <h2 id="form-feedback" class="heading-3">Kirim Masukan</h2>
            <form method="POST" action="{{ route('feedback.store') }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label for="category" class="label">Kategori</label>
                    <select id="category" name="category" required class="input">
                        @foreach ($categories as $categoryKey => $categoryLabel)
                            <option value="{{ $categoryKey }}" @selected(old('category') === $categoryKey)>{{ $categoryLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="subject" class="label">Subjek</label>
                    <input id="subject" name="subject" type="text" value="{{ old('subject') }}" required maxlength="150" class="input">
                </div>
                <div>
                    <label for="message" class="label">Isi masukan</label>
                    <textarea id="message" name="message" rows="5" required minlength="10" maxlength="2000" class="input">{{ old('message') }}</textarea>
                </div>
                <label class="flex cursor-pointer items-center gap-2 text-sm font-medium text-brand-950">
                    <input type="checkbox" name="is_anonymous" value="1" class="accent-brand-700" @checked(old('is_anonymous'))>
                    Kirim sebagai anonim (nama tidak ditampilkan ke admin)
                </label>
                <button type="submit" class="btn btn-primary w-full">Kirim</button>
            </form>
        </section>

        <section aria-labelledby="riwayat-feedback">
            <h2 id="riwayat-feedback" class="heading-3">Riwayat Masukan</h2>

            @if ($feedbacks->isEmpty())
                <div class="empty-state mt-4">
                    <span class="text-4xl" aria-hidden="true">&#128172;</span>
                    <p class="mt-3 font-medium text-stone-700">Belum ada masukan</p>
                    <p class="mt-1 text-sm text-stone-500">Masukan yang kamu kirim akan tampil di sini.</p>
                </div>
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($feedbacks as $feedback)
                        <article class="card p-4">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-brand-950">{{ $feedback->subject }}</p>
                                    <p class="text-xs text-brand-400">
                                        {{ $feedback->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
                                    </p>
                                </div>
                                @php
                                    $statusStyles = [
                                        'new' => 'badge-warning',
                                        'read' => 'badge-neutral',
                                        'in_progress' => 'badge-warning',
                                        'done' => 'badge-success',
                                    ];
                                @endphp
                                <span class="badge {{ $statusStyles[$feedback->status->value] ?? 'badge-neutral' }}">
                                    <span class="badge-dot"></span> {{ $feedback->status->label() }}
                                </span>
                            </div>
                            <p class="mt-2 text-sm text-stone-700">{{ $feedback->message }}</p>
                            @if ($feedback->admin_reply)
                                <div class="mt-3 rounded-xl bg-brand-50 p-3 text-sm">
                                    <p class="font-semibold text-brand-950">Balasan Admin</p>
                                    <p class="mt-1 text-brand-400">{{ $feedback->admin_reply }}</p>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
