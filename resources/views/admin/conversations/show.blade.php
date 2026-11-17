@extends('layouts.admin')

@section('title', 'Percakapan')

@section('content')
    <a href="{{ route('admin.conversations.index') }}" class="text-xs font-medium text-brand-500 hover:text-brand-900 hover:underline">&larr; Kembali ke Daftar Percakapan</a>

    <h1 class="heading-2 mt-2">
        Percakapan dengan <span class="text-brand-900">{{ $conversation->user?->name }}</span>
        <span class="badge {{ $conversation->status === 'open' ? 'badge-success' : 'badge-neutral' }} ml-1 align-middle">
            <span class="badge-dot"></span> {{ $conversation->status === 'open' ? 'Terbuka' : 'Ditutup' }}
        </span>
    </h1>

    <div class="card mx-auto mt-4 flex max-w-2xl flex-col">
        <div class="flex-1 space-y-3 p-4 sm:p-5" style="max-height: 60vh;">
            @forelse ($messages as $message)
                <div class="flex {{ $message->sender_role === 'admin' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] rounded-2xl px-4 py-2.5 text-sm {{ $message->sender_role === 'admin' ? 'bg-brand-700 text-white' : 'bg-brand-50 text-brand-950' }}">
                        <p class="whitespace-pre-line break-words">{{ $message->body }}</p>
                        <p class="mt-1 text-[11px] {{ $message->sender_role === 'admin' ? 'text-white/70' : 'text-brand-400' }}">
                            {{ $message->sender?->name }} &middot; {{ $message->created_at->timezone('Asia/Jakarta')->format('d M H:i') }}
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-brand-400">Belum ada pesan.</p>
            @endforelse
        </div>

        <form method="POST" action="{{ route('admin.conversations.reply', $conversation) }}" class="flex items-end gap-2 border-t border-brand-100 p-3 sm:p-4">
            @csrf
            <label for="body" class="sr-only">Balasan</label>
            <textarea id="body" name="body" rows="1" required maxlength="2000" placeholder="Tulis balasan..." class="input !mt-0 min-h-11 resize-none"></textarea>
            <button type="submit" class="btn btn-primary shrink-0 !min-h-11 px-4">Kirim</button>
        </form>
    </div>

    <div class="mx-auto mt-4 max-w-2xl">
        <form method="POST" action="{{ route('admin.conversations.status', $conversation) }}">
            @csrf
            <button type="submit" class="btn btn-secondary w-full">
                {{ $conversation->status === 'open' ? 'Tutup Percakapan' : 'Buka Kembali Percakapan' }}
            </button>
        </form>
    </div>
@endsection
