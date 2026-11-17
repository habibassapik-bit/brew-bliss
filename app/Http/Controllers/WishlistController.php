<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WishlistController extends Controller
{
    /**
     * Tampilkan wishlist milik customer yang sedang masuk.
     */
    public function index(): View
    {
        $items = Wishlist::query()
            ->where('user_id', auth()->id())
            ->with(['product.category', 'product.images'])
            ->latest()
            ->get();

        return view('wishlist.index', [
            'items' => $items,
        ]);
    }

    /**
     * Simpan produk ke wishlist (idempoten, tidak menduplikasi).
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $product = Product::query()->findOrFail($data['product_id']);

        if ($product->status === ProductStatus::Inactive) {
            throw ValidationException::withMessages([
                'product_id' => 'Produk tidak aktif dan tidak dapat disimpan.',
            ]);
        }

        $wishlist = Wishlist::query()->firstOrCreate([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
        ]);

        $message = $wishlist->wasRecentlyCreated
            ? "{$product->name} ditambahkan ke wishlist."
            : "{$product->name} sudah ada di wishlist.";

        return redirect()
            ->route('wishlist.index')
            ->with('status', $message);
    }

    /**
     * Hapus produk dari wishlist milik customer.
     */
    public function destroy(int $wishlist): RedirectResponse
    {
        $item = $this->findOwnedItem($wishlist);

        $product = $item->product;
        $item->delete();

        return redirect()
            ->route('wishlist.index')
            ->with('status', ($product?->name ?? 'Produk').' dihapus dari wishlist.');
    }

    /**
     * Pindahkan produk wishlist ke keranjang (tambah 1 unit lalu hapus dari wishlist).
     */
    public function moveToCart(int $wishlist): RedirectResponse
    {
        $item = $this->findOwnedItem($wishlist);

        $product = $item->product;

        if ($product === null || $product->status === ProductStatus::Inactive) {
            return redirect()
                ->route('wishlist.index')
                ->with('error', 'Produk sudah tidak tersedia dan tidak dapat dipindahkan ke keranjang.');
        }

        if (! $product->isOrderable()) {
            throw ValidationException::withMessages([
                'product_id' => $product->stock > 0
                    ? 'Produk sedang tidak dapat dipesan.'
                    : 'Stok produk habis.',
            ]);
        }

        $cart = Cart::query()->firstOrCreate(['user_id' => auth()->id()]);
        $cartItem = $cart->items()->where('product_id', $product->id)->first();
        $requestedQuantity = ($cartItem?->quantity ?? 0) + 1;

        if ($requestedQuantity > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => "Stok tidak mencukupi. Hanya {$product->stock} tersisa.",
            ]);
        }

        if ($cartItem === null) {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => 1,
            ]);
        } else {
            $cartItem->update(['quantity' => $requestedQuantity]);
        }

        $item->delete();

        return redirect()
            ->route('cart.index')
            ->with('status', "{$product->name} dipindahkan ke keranjang.");
    }

    /**
     * Cari item wishlist milik user yang sedang masuk.
     */
    private function findOwnedItem(int $wishlistId): Wishlist
    {
        return Wishlist::query()
            ->where('id', $wishlistId)
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }
}
