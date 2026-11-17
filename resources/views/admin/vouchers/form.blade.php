@extends('layouts.admin')

@section('title', $voucher->exists ? 'Edit Voucher' : 'Tambah Voucher')

@section('content')
    <a href="{{ route('admin.vouchers.index') }}" class="text-xs font-medium text-brand-500 hover:text-brand-900 hover:underline">&larr; Kembali ke Daftar Voucher</a>

    <h1 class="heading-1 mt-1">{{ $voucher->exists ? 'Edit Voucher' : 'Tambah Voucher' }}</h1>
    @if ($voucher->exists && $voucher->used_count > 0)
        <p class="mt-1 text-xs text-brand-400">
            Voucher ini sudah digunakan {{ $voucher->used_count }}&times;. Perubahan tidak memengaruhi diskon yang tersimpan pada pesanan lama.
        </p>
    @endif

    <form method="POST"
          action="{{ $voucher->exists ? route('admin.vouchers.update', $voucher) : route('admin.vouchers.store') }}"
          class="card mt-6 max-w-3xl p-5">
        @csrf
        @if ($voucher->exists)
            @method('PUT')
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="code" class="label">Kode voucher</label>
                <input id="code" name="code" type="text" value="{{ old('code', $voucher->code) }}" required maxlength="50"
                       placeholder="PROMO10K" class="input mt-1 uppercase">
            </div>
            <div>
                <label for="type" class="label">Jenis diskon</label>
                <select id="type" name="type" class="input mt-1">
                    @foreach (\App\Enums\VoucherType::cases() as $type)
                        <option value="{{ $type->value }}" @selected(old('type', $voucher->type?->value ?? \App\Enums\VoucherType::Fixed->value) === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="value" class="label">Nilai diskon</label>
                <input id="value" name="value" type="number" step="0.01" min="0" value="{{ old('value', $voucher->value) }}" required class="input mt-1">
                <p class="error-text !mt-1 !text-brand-400">Nominal (Rp) atau persentase sesuai jenis.</p>
            </div>
            <div>
                <label for="max_discount" class="label">Maksimum diskon (opsional)</label>
                <input id="max_discount" name="max_discount" type="number" step="0.01" min="0" value="{{ old('max_discount', $voucher->max_discount) }}" class="input mt-1">
            </div>
            <div>
                <label for="min_purchase" class="label">Minimum pembelian (Rp)</label>
                <input id="min_purchase" name="min_purchase" type="number" step="0.01" min="0" value="{{ old('min_purchase', $voucher->min_purchase ?? 0) }}" class="input mt-1">
            </div>
            <div>
                <label for="quota" class="label">Kuota penggunaan (opsional)</label>
                <input id="quota" name="quota" type="number" min="1" value="{{ old('quota', $voucher->quota) }}" class="input mt-1">
                @if ($voucher->exists)
                    <p class="error-text !mt-1 !text-brand-400">Sudah terpakai: {{ $voucher->used_count }}</p>
                @endif
            </div>
            <div>
                <label for="starts_at" class="label">Berlaku mulai (opsional)</label>
                <input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', optional($voucher->starts_at)->format('Y-m-d\TH:i')) }}" class="input mt-1">
            </div>
            <div>
                <label for="ends_at" class="label">Berlaku hingga (opsional)</label>
                <input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at', optional($voucher->ends_at)->format('Y-m-d\TH:i')) }}" class="input mt-1">
            </div>
            <div class="flex items-end sm:col-span-2">
                <label class="flex cursor-pointer items-center gap-2 text-sm font-medium text-brand-950">
                    <input type="checkbox" name="is_active" value="1" class="accent-brand-700" @checked(old('is_active', $voucher->exists ? $voucher->is_active : true))> Voucher aktif
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-5 w-full sm:w-auto">
            {{ $voucher->exists ? 'Simpan Perubahan' : 'Buat Voucher' }}
        </button>
    </form>
@endsection
