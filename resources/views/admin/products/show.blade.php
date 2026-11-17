@extends('layouts.admin')

@section('title', $product->name)

@section('content')
    <nav class="flex items-center gap-2 text-sm text-brand-400" aria-label="Breadcrumb">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-900">Dashboard</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('admin.products.index') }}" class="hover:text-brand-900">Produk</a>
        <span aria-hidden="true">/</span>
        <span class="font-medium text-brand-950">{{ $product->name }}</span>
    </nav>

    <div class="mt-3 flex flex-wrap items-start justify-between gap-3">
        <h1 class="heading-1">{{ $product->name }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-secondary !min-h-0 px-4 py-2 text-xs">Edit</a>
            <a href="{{ route('products.show', $product) }}" class="btn btn-ghost !min-h-0 px-4 py-2 text-xs">Lihat di Toko</a>
        </div>
    </div>

    <div class="mt-6 grid gap-6 md:grid-cols-3">
        <div class="md:col-span-1">
            <div class="card aspect-square overflow-hidden bg-gradient-to-br from-brand-100 to-stone-200">
                @if ($product->coverImage())
                    <img src="{{ $product->coverImage() }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                @else
                    <span class="grid h-full w-full place-items-center text-6xl font-semibold text-brand-500/50">{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</span>
                @endif
            </div>
            <p class="mt-2 text-center text-xs text-brand-400">Gambar utama produk</p>
        </div>

        <div class="md:col-span-2">
            <dl class="card divide-y divide-brand-50 text-sm">
                <div class="flex justify-between gap-4 px-4 py-3">
                    <dt class="text-brand-400">Kategori</dt>
                    <dd class="font-semibold text-brand-950">{{ $product->category?->name }}</dd>
                </div>
                <div class="flex justify-between gap-4 px-4 py-3">
                    <dt class="text-brand-400">Slug</dt>
                    <dd class="text-brand-950">{{ $product->slug }}</dd>
                </div>
                <div class="flex justify-between gap-4 px-4 py-3">
                    <dt class="text-brand-400">Harga Jual</dt>
                    <dd class="font-semibold text-brand-950">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</dd>
                </div>
                <div class="flex justify-between gap-4 px-4 py-3">
                    <dt class="text-brand-400">HPP / Modal</dt>
                    <dd class="text-brand-950">Rp {{ number_format((float) $product->cost_price, 0, ',', '.') }}</dd>
                </div>
                <div class="flex justify-between gap-4 px-4 py-3">
                    <dt class="text-brand-400">Stok</dt>
                    <dd class="font-semibold {{ $product->stock === 0 ? 'text-red-600' : 'text-brand-950' }}">{{ $product->stock }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4 px-4 py-3">
                    <dt class="text-brand-400">Status</dt>
                    <dd>
                        @if ($product->status === \App\Enums\ProductStatus::Available && $product->stock > 0)
                            <span class="badge badge-success"><span class="badge-dot"></span> Tersedia</span>
                        @elseif ($product->stock === 0 || $product->status === \App\Enums\ProductStatus::SoldOut)
                            <span class="badge badge-warning"><span class="badge-dot"></span> Habis</span>
                        @else
                            <span class="badge badge-neutral"><span class="badge-dot"></span> Nonaktif</span>
                        @endif
                    </dd>
                </div>
                <div class="px-4 py-3">
                    <dt class="text-brand-400">Deskripsi</dt>
                    <dd class="mt-1 text-brand-950">{{ $product->description ?? '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>
@endsection
