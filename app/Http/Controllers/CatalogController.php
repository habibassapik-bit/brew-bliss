<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Tampilkan katalog produk yang aktif dengan pencarian dan filter.
     */
    public function index(): View
    {
        $search = (string) request()->string('q');
        $categorySlug = (string) request()->string('category');
        $availability = (string) request()->string('availability');
        $sort = (string) request()->string('sort', 'newest');

        $products = Product::query()
            ->where('status', '!=', ProductStatus::Inactive->value)
            ->with('category')
            ->when($search !== '', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))
            ->when($categorySlug !== '', fn (Builder $query) => $query->whereHas(
                'category',
                fn (Builder $query) => $query
                    ->where('slug', $categorySlug)
                    ->where('is_active', true),
            ))
            ->when($availability === 'tersedia', fn (Builder $query) => $query
                ->where('status', ProductStatus::Available->value)
                ->where('stock', '>', 0))
            ->when($availability === 'habis', fn (Builder $query) => $query
                ->where(fn (Builder $query) => $query
                    ->where('status', ProductStatus::SoldOut->value)
                    ->orWhere('stock', 0)))
            ->when($sort === 'popular', fn (Builder $query) => $query
                ->withCount('orderItems')
                ->orderByDesc('order_items_count')
                ->orderByDesc('created_at'))
            ->when($sort === 'price_asc', fn (Builder $query) => $query->orderBy('price'))
            ->when($sort === 'price_desc', fn (Builder $query) => $query->orderByDesc('price'))
            ->when(! in_array($sort, ['popular', 'price_asc', 'price_desc'], true), fn (Builder $query) => $query->orderByDesc('created_at'))
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'search' => $search,
            'categorySlug' => $categorySlug,
            'availability' => $availability,
            'sort' => $sort,
            'wishlistProductIds' => auth()->check()
                ? auth()->user()->wishlists()->pluck('product_id')->all()
                : [],
        ]);
    }
}
