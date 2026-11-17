@extends('layouts.app')

@section('title', 'Bantuan')

@section('content')
    <h1 class="heading-1">Bantuan &amp; Customer Service</h1>
    <p class="mt-1 text-sm text-brand-400">
        Chat dengan tim kami. Jam layanan: {{ $storeHours }}. Pesan akan dibalas sesuai jam layanan.
    </p>

    <div class="card mx-auto mt-6 flex max-w-2xl flex-col">
        <div class="flex-1 space-y-3 overflow-y-auto p-4 sm:p-5" data-chat-messages style="max-height: 60vh;">
            @forelse ($messages as $message)
                <div class="flex {{ $message->sender_role === 'customer' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] rounded-2xl px-4 py-2.5 text-sm {{ $message->sender_role === 'customer' ? 'bg-brand-700 text-white' : 'bg-brand-50 text-brand-950' }}">
                        <p class="whitespace-pre-line break-words">{{ $message->body }}</p>
                        <p class="mt-1 text-[11px] {{ $message->sender_role === 'customer' ? 'text-white/70' : 'text-brand-400' }}">
                            {{ $message->sender?->name }} &middot; {{ $message->created_at->timezone('Asia/Jakarta')->format('d M H:i') }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <span class="text-4xl" aria-hidden="true">&#128172;</span>
                    <p class="mt-3 font-medium text-stone-700">Belum ada pesan</p>
                    <p class="mt-1 text-sm text-stone-500">Kirim pesan pertamamu di bawah.</p>
                </div>
            @endforelse
        </div>

        @if ($conversation->status === 'closed')
            <div class="border-t border-brand-100 p-4">
                <x-partials.status-banner type="info">
                    Percakapan ini telah ditutup oleh admin. Membuka halaman ini akan memulai percakapan baru.
                </x-partials.status-banner>
            </div>
        @else
            <form method="POST" action="{{ route('chat.store', $conversation) }}" class="flex items-end gap-2 border-t border-brand-100 p-3 sm:p-4">
                @csrf
                <label for="body" class="sr-only">Pesan</label>
                <textarea id="body" name="body" rows="1" required maxlength="2000" placeholder="Tulis pesan..." class="input !mt-0 min-h-11 resize-none"></textarea>
                <button type="submit" class="btn btn-primary shrink-0 !min-h-11 px-4">Kirim</button>
            </form>
        @endif
    </div>
@endsection
