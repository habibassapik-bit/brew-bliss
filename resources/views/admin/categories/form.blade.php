@extends('layouts.admin')

@section('title', $category->exists ? 'Edit Kategori' : 'Tambah Kategori')

@section('content')
    <nav class="flex items-center gap-2 text-sm text-brand-400" aria-label="Breadcrumb">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-900">Dashboard</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('admin.categories.index') }}" class="hover:text-brand-900">Kategori</a>
        <span aria-hidden="true">/</span>
        <span class="font-medium text-brand-950">{{ $category->exists ? 'Edit' : 'Tambah' }}</span>
    </nav>

    <h1 class="heading-1 mt-3">{{ $category->exists ? "Edit {$category->name}" : 'Tambah Kategori' }}</h1>

    <form method="POST"
          action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
          class="card mt-6 max-w-xl p-5 sm:p-6">
        @csrf
        @if ($category->exists)
            @method('PUT')
        @endif

        <div class="space-y-4">
            <div>
                <label for="name" class="label">Nama Kategori</label>
                <input id="name" name="name" type="text" value="{{ old('name', $category->name) }}" required maxlength="255" class="input">
                @error('name') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="sort_order" class="label">Urutan Tampil</label>
                <input id="sort_order" name="sort_order" type="number" min="0" max="9999" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="input">
                @error('sort_order') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-brand-950">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-stone-300 accent-brand-700">
                Kategori aktif (tampil di website customer)
            </label>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="btn btn-primary">{{ $category->exists ? 'Simpan Perubahan' : 'Buat Kategori' }}</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
@endsection
