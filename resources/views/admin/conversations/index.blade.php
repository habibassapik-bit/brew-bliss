@extends('layouts.admin')

@section('title', 'Customer Service')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="heading-1">Customer Service</h1>
            <p class="mt-1 text-sm text-brand-400">Percakapan pelanggan dengan toko.</p>
        </div>
    </div>

    @if ($conversations->isEmpty())
        <x-partials.empty-state
            icon="&#128172;"
            title="Belum ada percakapan"
            description="Percakapan pelanggan akan muncul di sini."
            class="mt-6" />
    @else
        <div class="card mt-6">
            <div class="table-scroll">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="border-b border-brand-100 text-xs uppercase tracking-wide text-brand-400">
                        <tr>
                            <th class="px-4 py-3 font-medium">Pelanggan</th>
                            <th class="px-4 py-3 font-medium">Pesan Terakhir</th>
                            <th class="px-4 py-3 font-medium">Aktivitas Terakhir</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-50">
                        @foreach ($conversations as $conversation)
                            @php
                                $lastMessage = $conversation->messages->sortByDesc('created_at')->first();
                                $unread = $conversation->messages->where('sender_role', 'customer')->whereNull('read_at')->count();
                            @endphp
                            <tr>
                                <td class="px-4 py-3 font-semibold text-brand-950">{{ $conversation->user?->name }}</td>
                                <td class="px-4 py-3 text-brand-400">{{ \Illuminate\Support\Str::limit($lastMessage?->body ?? '-', 60) }}</td>
                                <td class="px-4 py-3 text-brand-400">
                                    {{ $conversation->last_message_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }}
                                    @if ($unread > 0)
                                        <span class="badge badge-warning ml-1 !px-2 !py-0.5"><span class="badge-dot"></span> {{ $unread }} baru</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($conversation->status === 'open')
                                        <span class="badge badge-success"><span class="badge-dot"></span> Terbuka</span>
                                    @else
                                        <span class="badge badge-neutral"><span class="badge-dot"></span> Ditutup</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.conversations.show', $conversation) }}" class="btn btn-ghost !min-h-0 px-3 py-1.5 text-xs">Buka</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-brand-50 border-t border-brand-100 md:hidden">
                @foreach ($conversations as $conversation)
                    @php
                        $lastMessage = $conversation->messages->sortByDesc('created_at')->first();
                        $unread = $conversation->messages->where('sender_role', 'customer')->whereNull('read_at')->count();
                    @endphp
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-semibold text-brand-950">{{ $conversation->user?->name }}</p>
                            @if ($conversation->status === 'open')
                                <span class="badge badge-success"><span class="badge-dot"></span> Terbuka</span>
                            @else
                                <span class="badge badge-neutral"><span class="badge-dot"></span> Ditutup</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-brand-400">{{ \Illuminate\Support\Str::limit($lastMessage?->body ?? '-', 80) }}</p>
                        <p class="mt-1 text-xs text-brand-400">
                            {{ $conversation->last_message_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }}
                            @if ($unread > 0) &middot; {{ $unread }} pesan baru @endif
                        </p>
                        <a href="{{ route('admin.conversations.show', $conversation) }}" class="btn btn-secondary !min-h-0 mt-3 px-4 py-1.5 text-xs">Buka</a>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6">
            {{ $conversations->links() }}
        </div>
    @endif
@endsection
