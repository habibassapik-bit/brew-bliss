@extends('layouts.admin')

@section('title', 'Detail Masukan')

@section('content')
    <a href="{{ route('admin.feedbacks.index') }}" class="text-xs font-medium text-brand-500 hover:text-brand-900 hover:underline">&larr; Kembali ke Kritik &amp; Saran</a>

    <div class="mt-3 grid gap-6 lg:grid-cols-3">
        <section class="card p-5 lg:col-span-2" aria-labelledby="isi-masukan">
            <h2 id="isi-masukan" class="heading-3">{{ $feedback->subject }}</h2>
            <p class="mt-1 text-xs text-brand-400">
                Dari: <span class="font-semibold text-brand-950">{{ $feedback->user?->name ?? 'Anonim' }}</span>
                @if ($feedback->user) ({{ $feedback->user->email }}) @endif
                &middot; {{ $feedback->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
            </p>
            <p class="mt-4 whitespace-pre-line text-sm text-stone-700">{{ $feedback->message }}</p>

            @if ($feedback->admin_reply)
                <div class="mt-4 rounded-xl bg-brand-50 p-3 text-sm">
                    <p class="font-semibold text-brand-950">Balasan Admin</p>
                    <p class="mt-1 whitespace-pre-line text-brand-400">{{ $feedback->admin_reply }}</p>
                    <p class="mt-1 text-xs text-brand-400">
                        oleh {{ $feedback->repliedBy?->name ?? '-' }} &middot;
                        {{ optional($feedback->replied_at)->timezone('Asia/Jakarta')->format('d M Y, H:i') }}
                    </p>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.feedbacks.reply', $feedback) }}" class="mt-6 space-y-3 border-t border-dashed border-brand-100 pt-4">
                @csrf
                <div>
                    <label for="admin_reply" class="label">{{ $feedback->admin_reply ? 'Perbarui balasan' : 'Tulis balasan' }}</label>
                    <textarea id="admin_reply" name="admin_reply" rows="4" required minlength="5" maxlength="2000" class="input">{{ old('admin_reply', $feedback->admin_reply) }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary w-full sm:w-auto">Kirim Balasan</button>
            </form>
        </section>

        <aside class="space-y-6">
            <section class="card p-5" aria-labelledby="status-masukan">
                <h2 id="status-masukan" class="heading-3">Status Masukan</h2>
                <form method="POST" action="{{ route('admin.feedbacks.status', $feedback) }}" class="mt-3 space-y-3">
                    @csrf
                    <div>
                        <label for="status" class="label">Ubah status</label>
                        <select id="status" name="status" class="input mt-1">
                            @foreach (\App\Enums\FeedbackStatus::cases() as $feedbackStatus)
                                <option value="{{ $feedbackStatus->value }}" @selected($feedback->status === $feedbackStatus)>{{ $feedbackStatus->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-secondary w-full">Simpan Status</button>
                </form>
                <p class="mt-3 text-xs text-brand-400">
                    Dibuat {{ $feedback->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB &middot; Kategori: {{ $feedback->category }}
                </p>
            </section>
        </aside>
    </div>
@endsection
