<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * Tampilkan isi keranjang milik customer yang sedang masuk.
     */
    public function index(): View
    {
        $cart = $this->currentCart();

        $cart->load(['items.product.category', 'items.product.images']);

        $items = $cart->items;

        /** @var list<array{item: CartItem, product: Product|null, orderable: bool, line_total: float}> $lines */
        $lines = $items
            ->map(fn (CartItem $item): array => [
                'item' => $item,
                'product' => $item->product,
                'orderable' => $item->product !== null && $item->product->isOrderable(),
                'line_total' => $item->product !== null && $item->product->isOrderable()
                    ? (float) $item->product->price * $item->quantity
                    : 0.0,
            ]);

        $subtotal = (float) collect($lines)->where('orderable', true)->sum('line_total');

        return view('cart.index', [
            'lines' => $lines,
            'subtotal' => $subtotal,
        ]);
    }

    /**
     * Tambahkan produk ke keranjang dengan validasi stok.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $product = Product::query()->findOrFail($data['product_id']);

        $this->assertProductOrderable($product);

        $cart = $this->currentCart();

        $item = $cart->items()->where('product_id', $product->id)->first();

        $requestedQuantity = ($item?->quantity ?? 0) + $data['quantity'];

        if ($requestedQuantity > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => $product->stock > 0
                    ? "Stok tidak mencukupi. Hanya {$product->stock} tersisa."
                    : 'Stok produk habis.',
            ]);
        }

        if ($item === null) {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $data['quantity'],
                'note' => $data['note'] ?? null,
            ]);
        } else {
            $item->update([
                'quantity' => $requestedQuantity,
                'note' => $data['note'] ?? $item->note,
            ]);
        }

        return redirect()
            ->back()
            ->with('status', "{$product->name} ditambahkan ke keranjang.");
    }

    /**
     * Ubah jumlah atau catatan item keranjang.
     */
    public function update(Request $request, int $item): RedirectResponse
    {
        $cartItem = $this->findOwnedItem($item);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $product = $cartItem->product;

        $this->assertProductOrderable($product);

        if ($data['quantity'] > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => "Stok tidak mencukupi. Hanya {$product->stock} tersisa.",
            ]);
        }

        $cartItem->update([
            'quantity' => $data['quantity'],
            'note' => $data['note'] ?? $cartItem->note,
        ]);

        return redirect()
            ->route('cart.index')
            ->with('status', 'Keranjang diperbarui.');
    }

    /**
     * Hapus item dari keranjang.
     */
    public function destroy(int $item): RedirectResponse
    {
        $cartItem = $this->findOwnedItem($item);

        $cartItem->delete();

        return redirect()
            ->route('cart.index')
            ->with('status', 'Item dihapus dari keranjang.');
    }

    /**
     * Ambil atau buat keranjang milik user yang sedang masuk.
     */
    private function currentCart(): Cart
    {
        return Cart::query()->firstOrCreate(['user_id' => auth()->id()]);
    }

    /**
     * Cari item keranjang milik user yang sedang masuk.
     */
    private function findOwnedItem(int $itemId): CartItem
    {
        return CartItem::query()
            ->where('id', $itemId)
            ->whereHas('cart', fn ($query) => $query->where('user_id', auth()->id()))
            ->firstOrFail();
    }

    /**
     * Pastikan produk dapat dipesan (aktif dan stok tersedia).
     */
    private function assertProductOrderable(Product $product): void
    {
        if ($product->status === ProductStatus::Inactive) {
            throw ValidationException::withMessages([
                'product_id' => 'Produk tidak tersedia.',
            ]);
        }

        if (! $product->isOrderable()) {
            throw ValidationException::withMessages([
                'product_id' => $product->stock > 0
                    ? 'Produk sedang tidak dapat dipesan.'
                    : 'Stok produk habis.',
            ]);
        }
    }
}
