@extends('layouts.admin')

@section('title', 'Kritik & Saran')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="heading-1">Kritik &amp; Saran</h1>
            <p class="mt-1 text-sm text-brand-400">Kelola masukan dari pelanggan.</p>
        </div>
        @if ($pendingCount > 0)
            <span class="badge badge-warning"><span class="badge-dot"></span> {{ $pendingCount }} masukan baru</span>
        @endif
    </div>

    <form method="GET" action="{{ route('admin.feedbacks.index') }}" class="card mt-6 flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
        <select name="status" aria-label="Filter status" class="input !mt-0 sm:w-52">
            <option value="">Semua status</option>
            @foreach ($statuses as $feedbackStatus)
                <option value="{{ $feedbackStatus->value }}" {{ $statusFilter === $feedbackStatus->value ? 'selected' : '' }}>{{ $feedbackStatus->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-secondary !min-h-0 sm:w-24">Filter</button>
    </form>

    @if ($feedbacks->isEmpty())
        <x-partials.empty-state
            icon="&#128172;"
            title="Belum ada masukan"
            description="Masukan dari pelanggan akan muncul di sini."
            class="mt-6" />
    @else
        <div class="mt-6 space-y-4">
            @foreach ($feedbacks as $feedback)
                <article class="card p-4 sm:p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-brand-950">{{ $feedback->subject }}</p>
                            <p class="mt-0.5 text-xs text-brand-400">
                                {{ $feedback->user?->name ?? 'Anonim' }} &middot;
                                {{ $feedback->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="badge badge-neutral"><span class="badge-dot"></span> {{ $feedback->category }}</span>
                            <span class="badge {{ match ($feedback->status) {
                                \App\Enums\FeedbackStatus::New => 'badge-warning',
                                \App\Enums\FeedbackStatus::Done => 'badge-success',
                                default => 'badge-neutral',
                            } }}"><span class="badge-dot"></span> {{ $feedback->status->label() }}</span>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-stone-700">{{ \Illuminate\Support\Str::limit($feedback->message, 160) }}</p>
                    <div class="mt-3 flex flex-wrap items-center gap-3 border-t border-dashed border-brand-100 pt-3">
                        <a href="{{ route('admin.feedbacks.show', $feedback) }}" class="btn btn-secondary !min-h-0 px-4 py-2 text-xs">Detail</a>
                        @if ($feedback->admin_reply)
                            <span class="text-xs text-brand-400">Sudah dibalas</span>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $feedbacks->links() }}
        </div>
    @endif
@endsection
