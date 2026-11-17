@extends('layouts.admin')

@section('title', 'Produk')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="heading-1">Produk</h1>
            <p class="mt-1 text-sm text-brand-400">Kelola produk, harga, HPP, stok, dan status.</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Tambah Produk</a>
    </div>

    <form method="GET" action="{{ route('admin.products.index') }}" class="card mt-6 flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
        <input type="search" name="q" value="{{ $search }}" placeholder="Cari nama produk..." aria-label="Cari nama produk" class="input !mt-0 flex-1">
        <select name="category" aria-label="Filter kategori" class="input !mt-0 sm:w-48">
            <option value="">Semua kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" {{ $categoryId === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
            @endforeach
        </select>
        <select name="status" aria-label="Filter status" class="input !mt-0 sm:w-40">
            <option value="">Semua status</option>
            @foreach (\App\Enums\ProductStatus::cases() as $productStatus)
                <option value="{{ $productStatus->value }}" {{ $status === $productStatus->value ? 'selected' : '' }}>{{ $productStatus->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-secondary !min-h-0 sm:w-28">Filter</button>
    </form>

    @if ($products->isEmpty())
        <x-partials.empty-state
            icon="&#128230;"
            title="Produk tidak ditemukan"
            description="Tambahkan produk baru atau ubah filter pencarian."
            actionHref="{{ route('admin.products.create') }}"
            actionLabel="Tambah Produk"
            class="mt-6" />
    @else
        <div class="card mt-6">
            <div class="table-scroll">
                <table class="w-full min-w-[880px] text-left text-sm">
                    <thead class="border-b border-brand-100 text-xs uppercase tracking-wide text-brand-400">
                        <tr>
                            <th class="px-4 py-3 font-medium">Gambar</th>
                            <th class="px-4 py-3 font-medium">Nama</th>
                            <th class="px-4 py-3 font-medium">Kategori</th>
                            <th class="px-4 py-3 font-medium">Harga</th>
                            <th class="px-4 py-3 font-medium">HPP</th>
                            <th class="px-4 py-3 font-medium">Stok</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-50">
                        @foreach ($products as $product)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="h-11 w-11 overflow-hidden rounded-lg bg-brand-100">
                                        @if ($product->coverImage())
                                            <img src="{{ $product->coverImage() }}" alt="{{ $product->name }}" class="h-full w-full object-cover" loading="lazy">
                                        @else
                                            <span class="grid h-full w-full place-items-center text-sm font-semibold text-brand-500/70">{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-semibold text-brand-950">{{ $product->name }}</td>
                                <td class="px-4 py-3 text-brand-400">{{ $product->category?->name }}</td>
                                <td class="px-4 py-3 text-brand-950">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-brand-400">Rp {{ number_format((float) $product->cost_price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 {{ $product->stock === 0 ? 'font-semibold text-red-600' : 'text-brand-950' }}">{{ $product->stock }}</td>
                                <td class="px-4 py-3">
                                    @if ($product->status === \App\Enums\ProductStatus::Available && $product->stock > 0)
                                        <span class="badge badge-success"><span class="badge-dot"></span> Tersedia</span>
                                    @elseif ($product->stock === 0)
                                        <span class="badge badge-warning"><span class="badge-dot"></span> Habis</span>
                                    @elseif ($product->status === \App\Enums\ProductStatus::SoldOut)
                                        <span class="badge badge-warning"><span class="badge-dot"></span> Habis</span>
                                    @else
                                        <span class="badge badge-neutral"><span class="badge-dot"></span> Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.products.show', $product) }}" class="btn btn-ghost !min-h-0 px-3 py-1.5 text-xs">Lihat</a>
                                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-secondary !min-h-0 px-3 py-1.5 text-xs">Edit</a>
                                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                              onsubmit="return confirm('Hapus produk {{ $product->name }}? Riwayat transaksi tetap aman.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger !min-h-0 px-3 py-1.5 text-xs">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-brand-50 border-t border-brand-100 md:hidden">
                @foreach ($products as $product)
                    <div class="p-4">
                        <div class="flex gap-3">
                            <div class="h-14 w-14 shrink-0 overflow-hidden rounded-xl bg-brand-100">
                                @if ($product->coverImage())
                                    <img src="{{ $product->coverImage() }}" alt="{{ $product->name }}" class="h-full w-full object-cover" loading="lazy">
                                @else
                                    <span class="grid h-full w-full place-items-center text-base font-semibold text-brand-500/70">{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</span>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="truncate font-semibold text-brand-950">{{ $product->name }}</p>
                                    @if ($product->status === \App\Enums\ProductStatus::Available && $product->stock > 0)
                                        <span class="badge badge-success"><span class="badge-dot"></span> Tersedia</span>
                                    @elseif ($product->stock === 0 || $product->status === \App\Enums\ProductStatus::SoldOut)
                                        <span class="badge badge-warning"><span class="badge-dot"></span> Habis</span>
                                    @else
                                        <span class="badge badge-neutral"><span class="badge-dot"></span> Nonaktif</span>
                                    @endif
                                </div>
                                <p class="text-xs text-brand-400">{{ $product->category?->name }} &middot; Stok {{ $product->stock }}</p>
                                <p class="mt-1 text-sm font-bold text-brand-900">Rp {{ number_format((float) $product->price, 0, ',', '.') }} <span class="text-xs font-normal text-brand-400">(HPP Rp {{ number_format((float) $product->cost_price, 0, ',', '.') }})</span></p>
                            </div>
                        </div>
                        <div class="mt-3 flex gap-2">
                            <a href="{{ route('admin.products.show', $product) }}" class="btn btn-ghost !min-h-0 px-3 py-1.5 text-xs">Lihat</a>
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-secondary !min-h-0 px-3 py-1.5 text-xs">Edit</a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                  onsubmit="return confirm('Hapus produk {{ $product->name }}? Riwayat transaksi tetap aman.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger !min-h-0 px-3 py-1.5 text-xs">Hapus</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6">
            {{ $products->links() }}
        </div>
    @endif
@endsection
