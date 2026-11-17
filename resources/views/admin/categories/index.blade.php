@extends('layouts.admin')

@section('title', 'Kategori')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="heading-1">Kategori</h1>
            <p class="mt-1 text-sm text-brand-400">Kelola kategori produk toko.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">Tambah Kategori</a>
    </div>

    @if (session('error'))
        <x-partials.status-banner type="error" class="mt-4">{{ session('error') }}</x-partials.status-banner>
    @endif

    @if ($categories->isEmpty())
        <x-partials.empty-state
            icon="&#127869;"
            title="Belum ada kategori"
            description="Tambahkan kategori pertama untuk mengelompokkan produk."
            actionHref="{{ route('admin.categories.create') }}"
            actionLabel="Tambah Kategori"
            class="mt-6" />
    @else
        <div class="card mt-6">
            <div class="table-scroll">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="border-b border-brand-100 text-xs uppercase tracking-wide text-brand-400">
                        <tr>
                            <th class="px-4 py-3 font-medium">Nama</th>
                            <th class="px-4 py-3 font-medium">Slug</th>
                            <th class="px-4 py-3 font-medium">Produk</th>
                            <th class="px-4 py-3 font-medium">Urutan</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-50">
                        @foreach ($categories as $category)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-brand-950">{{ $category->name }}</td>
                                <td class="px-4 py-3 text-brand-400">{{ $category->slug }}</td>
                                <td class="px-4 py-3 text-brand-950">{{ $category->products_count + $category->products_trashed_count }}</td>
                                <td class="px-4 py-3 text-brand-950">{{ $category->sort_order }}</td>
                                <td class="px-4 py-3">
                                    @if ($category->is_active)
                                        <span class="badge badge-success"><span class="badge-dot"></span> Aktif</span>
                                    @else
                                        <span class="badge badge-neutral"><span class="badge-dot"></span> Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-secondary !min-h-0 px-3 py-1.5 text-xs">Edit</a>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                              onsubmit="return confirm('Hapus kategori {{ $category->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger !min-h-0 px-3 py-1.5 text-xs" {{ ($category->products_count + $category->products_trashed_count) > 0 ? 'disabled title="Masih dipakai produk"' : '' }}>Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-brand-50 border-t border-brand-100 md:hidden">
                @foreach ($categories as $category)
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-brand-950">{{ $category->name }}</p>
                                <p class="text-xs text-brand-400">{{ $category->slug }} &middot; {{ $category->products_count + $category->products_trashed_count }} produk &middot; urutan {{ $category->sort_order }}</p>
                            </div>
                            @if ($category->is_active)
                                <span class="badge badge-success"><span class="badge-dot"></span> Aktif</span>
                            @else
                                <span class="badge badge-neutral"><span class="badge-dot"></span> Nonaktif</span>
                            @endif
                        </div>
                        <div class="mt-3 flex gap-2">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-secondary !min-h-0 px-3 py-1.5 text-xs">Edit</a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                  onsubmit="return confirm('Hapus kategori {{ $category->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger !min-h-0 px-3 py-1.5 text-xs" {{ ($category->products_count + $category->products_trashed_count) > 0 ? 'disabled title="Masih dipakai produk"' : '' }}>Hapus</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection
