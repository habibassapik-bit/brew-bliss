@extends('layouts.admin')

@section('title', $product->exists ? 'Edit Produk' : 'Tambah Produk')

@section('content')
    <nav class="flex items-center gap-2 text-sm text-brand-400" aria-label="Breadcrumb">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-900">Dashboard</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('admin.products.index') }}" class="hover:text-brand-900">Produk</a>
        <span aria-hidden="true">/</span>
        <span class="font-medium text-brand-950">{{ $product->exists ? 'Edit' : 'Tambah' }}</span>
    </nav>

    <h1 class="heading-1 mt-3">{{ $product->exists ? "Edit {$product->name}" : 'Tambah Produk' }}</h1>

    <form method="POST"
          action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
          enctype="multipart/form-data"
          class="card mt-6 max-w-2xl p-5 sm:p-6">
        @csrf
        @if ($product->exists)
            @method('PUT')
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="label">Nama Produk</label>
                <input id="name" name="name" type="text" value="{{ old('name', $product->name) }}" required maxlength="255" class="input">
                @error('name') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="category_id" class="label">Kategori</label>
                <select id="category_id" name="category_id" required class="input">
                    <option value="">Pilih kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}{{ $category->is_active ? '' : ' (nonaktif)' }}
                        </option>
                    @endforeach
                </select>
                @error('category_id') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="status" class="label">Status Produk</label>
                <select id="status" name="status" required class="input">
                    @foreach (\App\Enums\ProductStatus::cases() as $productStatus)
                        <option value="{{ $productStatus->value }}" {{ old('status', $product->status) === $productStatus->value ? 'selected' : '' }}>
                            {{ $productStatus->label() }}
                        </option>
                    @endforeach
                </select>
                @error('status') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="price" class="label">Harga Jual (Rp)</label>
                <input id="price" name="price" type="number" min="0" max="99999999" step="0.01" value="{{ old('price', $product->price) }}" required class="input">
                @error('price') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="cost_price" class="label">HPP / Modal per Unit (Rp)</label>
                <input id="cost_price" name="cost_price" type="number" min="0" max="99999999" step="0.01" value="{{ old('cost_price', $product->cost_price) }}" required class="input">
                @error('cost_price') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="stock" class="label">Stok</label>
                <input id="stock" name="stock" type="number" min="0" max="999999" value="{{ old('stock', $product->stock ?? 0) }}" required class="input">
                @error('stock') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="description" class="label">Deskripsi</label>
                <textarea id="description" name="description" rows="4" maxlength="5000" class="input">{{ old('description', $product->description) }}</textarea>
                @error('description') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="image" class="label">Gambar Produk (JPG/PNG/WebP, maks 2 MB)</label>
                @if ($product->image)
                    <div class="mt-2 flex items-center gap-3">
                        <img src="{{ $product->coverImage() }}" alt="Gambar {{ $product->name }}" class="h-16 w-16 rounded-xl object-cover">
                        <p class="text-xs text-brand-400">Kosongkan kolom di bawah jika ingin mempertahankan gambar ini.</p>
                    </div>
                @endif
                <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp" class="input !py-2 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-brand-700">
                @error('image') <p class="error-text">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="btn btn-primary">{{ $product->exists ? 'Simpan Perubahan' : 'Buat Produk' }}</button>
            <a href="{{ $product->exists ? route('admin.products.show', $product) : route('admin.products.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
@endsection
