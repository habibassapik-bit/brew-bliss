<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\Review;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Tampilkan halaman detail produk.
     */
    public function show(Product $product): View
    {
        abort_if($product->status === ProductStatus::Inactive, 404);

        $product->load(['category', 'images']);

        $reviews = $product->reviews()
            ->with('user')
            ->latest()
            ->paginate(5);

        $averageRating = (float) $product->reviews()->avg('rating');
        $reviewCount = $product->reviews()->count();

        [$canReview, $ownReview] = auth()->check()
            ? $this->reviewEligibility($product)
            : [false, null];

        $relatedProducts = Product::query()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', ProductStatus::Available->value)
            ->where('stock', '>', 0)
            ->with('category')
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('products.show', [
            'product' => $product,
            'reviews' => $reviews,
            'averageRating' => $averageRating,
            'reviewCount' => $reviewCount,
            'canReview' => $canReview,
            'ownReview' => $ownReview,
            'relatedProducts' => $relatedProducts,
            'wishlisted' => auth()->check()
                ? auth()->user()->wishlists()->where('product_id', $product->id)->exists()
                : false,
            'wishlistProductIds' => auth()->check()
                ? auth()->user()->wishlists()->pluck('product_id')->all()
                : [],
        ]);
    }

    /**
     * Tentukan apakah customer yang masuk boleh mengulas produk dan apakah sudah memiliki ulasan.
     *
     * @return array{0: bool, 1: Review|null}
     */
    private function reviewEligibility(Product $product): array
    {
        $ownReview = $product->reviews()
            ->where('user_id', auth()->id())
            ->first();

        if ($ownReview !== null) {
            return [false, $ownReview];
        }

        $purchased = $product->orderItems()
            ->whereHas('order', fn ($query) => $query
                ->where('user_id', auth()->id())
                ->where('status', OrderStatus::Completed->value))
            ->exists();

        return [$purchased, null];
    }
}
