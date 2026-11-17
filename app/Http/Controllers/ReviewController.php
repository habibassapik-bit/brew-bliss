<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    /**
     * Simpan ulasan produk dari customer yang pernah membeli (pesanan selesai).
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $orderItem = OrderItem::query()
            ->where('product_id', $product->id)
            ->whereHas('order', fn ($query) => $query
                ->where('user_id', auth()->id())
                ->where('status', OrderStatus::Completed->value))
            ->first();

        if ($orderItem === null) {
            throw ValidationException::withMessages([
                'review' => 'Ulasan hanya dapat diberikan untuk produk dari pesanan yang telah selesai.',
            ]);
        }

        $exists = Review::query()
            ->where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'review' => 'Kamu sudah pernah memberikan ulasan untuk produk ini.',
            ]);
        }

        Review::query()->create([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'order_id' => $orderItem->order_id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return redirect()
            ->route('products.show', $product)
            ->with('status', 'Terima kasih! Ulasanmu berhasil dikirim.');
    }
}
