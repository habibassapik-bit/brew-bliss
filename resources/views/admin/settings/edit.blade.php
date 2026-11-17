@extends('layouts.admin')

@section('title', 'Pengaturan Toko')

@section('content')
    @php
        /** @var \Illuminate\Support\Collection<string, string> $values */
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="heading-1">Pengaturan Toko</h1>
            <p class="mt-1 text-sm text-brand-400">Informasi toko ditampilkan pada beranda, footer, dan tombol WhatsApp.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">&larr; Kembali</a>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="card mt-6 max-w-2xl p-5">
        @csrf
        @method('PUT')

        <div class="grid gap-4">
            <div>
                <label for="store_name" class="label">Nama toko</label>
                <input id="store_name" name="store_name" type="text" value="{{ old('store_name', $values->get('store_name')) }}" class="input" required maxlength="255">
            </div>

            <div>
                <label for="store_description" class="label">Deskripsi toko</label>
                <textarea id="store_description" name="store_description" rows="3" class="input" maxlength="1000">{{ old('store_description', $values->get('store_description')) }}</textarea>
            </div>

            <div>
                <label for="store_address" class="label">Alamat</label>
                <textarea id="store_address" name="store_address" rows="2" class="input" required maxlength="255">{{ old('store_address', $values->get('store_address')) }}</textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="store_phone" class="label">Nomor telepon</label>
                    <input id="store_phone" name="store_phone" type="tel" value="{{ old('store_phone', $values->get('store_phone')) }}" class="input" required maxlength="20" placeholder="081234567890">
                </div>
                <div>
                    <label for="store_open_hours" class="label">Jam operasional</label>
                    <input id="store_open_hours" name="store_open_hours" type="text" value="{{ old('store_open_hours', $values->get('store_open_hours')) }}" class="input" required maxlength="100" placeholder="Setiap hari 08.00 - 18.00 WIB">
                </div>
            </div>

            <div>
                <label for="store_whatsapp" class="label">Nomor WhatsApp (tombol mengambang)</label>
                <input id="store_whatsapp" name="store_whatsapp" type="tel" value="{{ old('store_whatsapp', $values->get('store_whatsapp')) }}" class="input" maxlength="20" placeholder="081234567890">
                <p class="mt-1 text-xs text-brand-400">Kosongkan untuk menyembunyikan tombol WhatsApp di sisi pelanggan.</p>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
@endsection
