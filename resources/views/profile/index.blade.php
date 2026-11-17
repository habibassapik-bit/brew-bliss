@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
    <h1 class="heading-1">Profil Saya</h1>
    <p class="mt-1 text-sm text-brand-400">Kelola data diri dan alamat pengirimanmu.</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="card p-5" aria-labelledby="data-profil">
            <h2 id="data-profil" class="heading-3">Data Profil</h2>
            <form method="POST" action="{{ route('profile.update') }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label for="name" class="label">Nama lengkap</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="255" class="input">
                </div>
                <div>
                    <label for="email" class="label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255" class="input">
                </div>
                <div>
                    <label for="phone" class="label">Nomor telepon</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" required minlength="9" maxlength="20" inputmode="tel" placeholder="08xxxxxxxxxx" class="input">
                </div>
                <button type="submit" class="btn btn-primary w-full sm:w-auto">Simpan Profil</button>
            </form>
        </section>

        <section class="card p-5" aria-labelledby="kata-sandi">
            <h2 id="kata-sandi" class="heading-3">Ubah Kata Sandi</h2>
            <form method="POST" action="{{ route('profile.password') }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label for="current_password" class="label">Kata sandi saat ini</label>
                    <input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="input">
                </div>
                <div>
                    <label for="password" class="label">Kata sandi baru</label>
                    <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" class="input">
                </div>
                <div>
                    <label for="password_confirmation" class="label">Konfirmasi kata sandi baru</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="input">
                </div>
                <button type="submit" class="btn btn-secondary w-full sm:w-auto">Ubah Kata Sandi</button>
            </form>
        </section>
    </div>

    <section class="card mt-6 p-5" aria-labelledby="alamat">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 id="alamat" class="heading-3">Alamat Pengiriman</h2>
        </div>

        @if ($addresses->isEmpty())
            <p class="mt-3 text-sm text-brand-400">Kamu belum menyimpan alamat. Tambahkan alamat di bawah.</p>
        @else
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach ($addresses as $address)
                    <div class="rounded-xl border border-stone-200 p-4">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-semibold text-brand-950">
                                {{ $address->recipient_name }}
                                @if ($address->label) <span class="badge badge-neutral">{{ $address->label }}</span> @endif
                            </p>
                            @if ($address->is_default)
                                <span class="badge badge-success"><span class="badge-dot"></span> Utama</span>
                            @endif
                        </div>
                        <p class="mt-1 text-xs text-brand-400">{{ $address->phone }}</p>
                        <p class="mt-1 text-sm text-brand-400">{{ $address->address }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @if (! $address->is_default)
                                <form method="POST" action="{{ route('profile.addresses.default', $address) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-ghost !min-h-0 px-3 py-1.5 text-xs">Jadikan Utama</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('profile.addresses.destroy', $address) }}"
                                  onsubmit="return confirm('Hapus alamat ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger !min-h-0 border border-red-200 px-3 py-1.5 text-xs">Hapus</button>
                            </form>
                        </div>

                        <details class="mt-3 border-t border-dashed border-brand-100 pt-3">
                            <summary class="cursor-pointer text-xs font-medium text-brand-700 hover:text-brand-900">Edit alamat</summary>
                            <form method="POST" action="{{ route('profile.addresses.update', $address) }}" class="mt-3 space-y-3">
                                @csrf
                                @method('PATCH')
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label for="edit_recipient_{{ $address->id }}" class="label">Nama penerima</label>
                                        <input id="edit_recipient_{{ $address->id }}" name="recipient_name" type="text" value="{{ $address->recipient_name }}" required maxlength="255" class="input">
                                    </div>
                                    <div>
                                        <label for="edit_phone_{{ $address->id }}" class="label">Telepon penerima</label>
                                        <input id="edit_phone_{{ $address->id }}" name="phone" type="tel" value="{{ $address->phone }}" required minlength="9" maxlength="20" inputmode="tel" class="input">
                                    </div>
                                    <div>
                                        <label for="edit_label_{{ $address->id }}" class="label">Label (opsional)</label>
                                        <input id="edit_label_{{ $address->id }}" name="label" type="text" value="{{ $address->label }}" maxlength="50" class="input">
                                    </div>
                                    <div class="flex items-end">
                                        <label class="flex cursor-pointer items-center gap-2 text-sm font-medium text-brand-950">
                                            <input type="checkbox" name="is_default" value="1" class="accent-brand-700" @checked($address->is_default)> Jadikan alamat utama
                                        </label>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label for="edit_address_{{ $address->id }}" class="label">Alamat lengkap</label>
                                        <textarea id="edit_address_{{ $address->id }}" name="address" rows="2" required maxlength="500" class="input">{{ $address->address }}</textarea>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-secondary !min-h-0 px-4 py-1.5 text-xs">Simpan Perubahan</button>
                            </form>
                        </details>
                    </div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('profile.addresses.store') }}" class="mt-6 space-y-3 border-t border-dashed border-brand-100 pt-4">
            @csrf
            <p class="font-semibold text-brand-950">Tambah Alamat Baru</p>
            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label for="recipient_name" class="label">Nama penerima</label>
                    <input id="recipient_name" name="recipient_name" type="text" value="{{ old('recipient_name') }}" required maxlength="255" class="input">
                </div>
                <div>
                    <label for="address_phone" class="label">Telepon penerima</label>
                    <input id="address_phone" name="phone" type="tel" value="{{ old('phone') }}" required minlength="9" maxlength="20" inputmode="tel" class="input">
                </div>
                <div>
                    <label for="address_label" class="label">Label (opsional)</label>
                    <input id="address_label" name="label" type="text" value="{{ old('label') }}" maxlength="50" placeholder="Rumah, Kantor" class="input">
                </div>
                <div class="flex items-end">
                    <label class="flex cursor-pointer items-center gap-2 text-sm font-medium text-brand-950">
                        <input type="checkbox" name="is_default" value="1" class="accent-brand-700"> Jadikan alamat utama
                    </label>
                </div>
                <div class="sm:col-span-2">
                    <label for="address_text" class="label">Alamat lengkap</label>
                    <textarea id="address_text" name="address" rows="2" required maxlength="500" placeholder="Nama jalan, nomor rumah, kecamatan, kode pos" class="input">{{ old('address') }}</textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-full sm:w-auto">Tambah Alamat</button>
        </form>
    </section>
@endsection
