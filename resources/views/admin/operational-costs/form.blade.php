@extends('layouts.admin')

@section('title', $cost->exists ? 'Edit Biaya Operasional' : 'Tambah Biaya Operasional')

@section('content')
    @php
        /** @var \App\Models\OperationalCost $cost */
        /** @var array<string, string> $categories */
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="heading-1">{{ $cost->exists ? 'Edit Biaya Operasional' : 'Tambah Biaya Operasional' }}</h1>
            <p class="mt-1 text-sm text-brand-400">Biaya operasional dipisahkan dari HPP produk.</p>
        </div>
        <a href="{{ route('admin.costs.index') }}" class="btn btn-secondary">&larr; Kembali</a>
    </div>

    <x-partials.form-errors class="mt-6" />

    <form method="POST" action="{{ $cost->exists ? route('admin.costs.update', $cost) : route('admin.costs.store') }}"
          class="card mt-6 max-w-2xl p-5">
        @csrf
        @if ($cost->exists)
            @method('PUT')
        @endif

        <div class="grid gap-4">
            <div>
                <label for="name" class="label">Nama biaya</label>
                <input id="name" name="name" type="text" value="{{ old('name', $cost->name) }}" class="input" required maxlength="255" placeholder="Contoh: Gas dapur, kemasan, listrik">
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="category" class="label">Kategori</label>
                    <select id="category" name="category" class="input">
                        @foreach ($categories as $value => $label)
                            <option value="{{ $value }}" {{ old('category', $cost->category ?? 'operational') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="amount" class="label">Nominal (Rp)</label>
                    <input id="amount" name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount', $cost->exists ? (string) $cost->amount : '') }}" class="input" required>
                </div>
            </div>

            <div>
                <label for="incurred_at" class="label">Tanggal</label>
                <input id="incurred_at" name="incurred_at" type="date" value="{{ old('incurred_at', $cost->incurred_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="input" required>
            </div>

            <div>
                <label for="note" class="label">Catatan (opsional)</label>
                <textarea id="note" name="note" rows="3" class="input" maxlength="1000">{{ old('note', $cost->note) }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="btn btn-primary">{{ $cost->exists ? 'Simpan Perubahan' : 'Simpan Biaya' }}</button>
            <a href="{{ route('admin.costs.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
@endsection
