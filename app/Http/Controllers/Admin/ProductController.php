<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Tampilkan daftar produk dengan pencarian dan filter.
     */
    public function index(): View
    {
        $search = (string) request()->string('q');
        $categoryId = request()->integer('category');
        $status = (string) request()->string('status');

        $products = Product::query()
            ->with('category')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when($categoryId > 0, fn ($query) => $query->where('category_id', $categoryId))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $categories = Category::query()->orderBy('name')->get();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => $categories,
            'search' => $search,
            'categoryId' => $categoryId,
            'status' => $status,
        ]);
    }

    /**
     * Tampilkan form produk baru.
     */
    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product,
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * Simpan produk baru beserta gambar opsional.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $data['slug'] = $this->uniqueSlug($data['name']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::query()->create($data);

        ActivityLogger::log('product.created', $product, [
            'description' => "Menambah produk {$product->name}.",
        ]);

        return redirect()
            ->route('admin.products.index')
            ->with('status', "Produk {$data['name']} berhasil dibuat.");
    }

    /**
     * Tampilkan detail produk.
     */
    public function show(Product $product): View
    {
        $product->load('category');

        return view('admin.products.show', [
            'product' => $product,
        ]);
    }

    /**
     * Tampilkan form edit produk.
     */
    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * Perbarui produk dan gambar jika diganti.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request);

        if ($data['name'] !== $product->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $product->id);
        }

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        ActivityLogger::log('product.updated', $product, [
            'description' => "Memperbarui produk {$product->name}.",
        ]);

        return redirect()
            ->route('admin.products.show', $product)
            ->with('status', "Produk {$product->name} berhasil diperbarui.");
    }

    /**
     * Soft delete produk agar riwayat transaksi tetap aman.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        ActivityLogger::log('product.deactivated', $product, [
            'description' => "Menonaktifkan produk {$product->name} dari katalog.",
        ]);

        return redirect()
            ->route('admin.products.index')
            ->with('status', "Produk {$product->name} dinonaktifkan dari katalog. Riwayat transaksi tetap tersimpan.");
    }

    /**
     * Validasi data produk.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $statuses = implode(',', array_column(ProductStatus::cases(), 'value'));

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'cost_price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'stock' => ['required', 'integer', 'min:0', 'max:999999'],
            'status' => ['required', 'string', 'in:'.$statuses],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
    }

    /**
     * Buat slug unik dari nama produk.
     */
    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = str($name)->slug();
        $slug = $base;
        $suffix = 1;

        while (Product::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.++$suffix;
        }

        return $slug;
    }
}
