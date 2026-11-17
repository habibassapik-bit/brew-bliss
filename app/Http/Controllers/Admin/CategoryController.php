<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Tampilkan daftar kategori.
     */
    public function index(): View
    {
        $categories = Category::query()
            ->withCount('products')
            ->withCount(['products as products_trashed_count' => fn ($query) => $query->onlyTrashed()])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Tampilkan form kategori baru.
     */
    public function create(): View
    {
        return view('admin.categories.form', [
            'category' => new Category,
        ]);
    }

    /**
     * Simpan kategori baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $data['slug'] = $this->uniqueSlug($data['name']);

        $category = Category::query()->create($data);

        ActivityLogger::log('category.created', $category, [
            'description' => "Menambah kategori {$category->name}.",
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', "Kategori {$data['name']} berhasil dibuat.");
    }

    /**
     * Tampilkan form edit kategori.
     */
    public function edit(Category $category): View
    {
        return view('admin.categories.form', [
            'category' => $category,
        ]);
    }

    /**
     * Perbarui kategori.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $this->validated($request, $category);

        if ($data['name'] !== $category->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $category->id);
        }

        $category->update($data);

        ActivityLogger::log('category.updated', $category, [
            'description' => "Memperbarui kategori {$category->name}.",
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', "Kategori {$category->name} berhasil diperbarui.");
    }

    /**
     * Hapus kategori (diblokir jika masih dipakai produk, termasuk yang sudah dihapus).
     */
    public function destroy(Category $category): RedirectResponse
    {
        $usedCount = $category->products()->withTrashed()->count();

        if ($usedCount > 0) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', "Kategori {$category->name} masih dipakai {$usedCount} produk dan tidak dapat dihapus.");
        }

        $category->delete();

        ActivityLogger::log('category.deleted', $category, [
            'description' => "Menghapus kategori {$category->name}.",
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', "Kategori {$category->name} berhasil dihapus.");
    }

    /**
     * Validasi data kategori.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * Buat slug unik dari nama kategori.
     */
    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = str($name)->slug();
        $slug = $base;
        $suffix = 1;

        while (Category::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.++$suffix;
        }

        return $slug;
    }
}
