<?php

use App\Enums\ProductStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

$product = Product::query()->whereNotNull('image')->first();
echo 'product: '.$product->id.' '.$product->name.' image='.$product->image."\n";

$cart = Cart::query()->first() ?? Cart::query()->create(['user_id' => 1]);
$item = $cart->items()->where('product_id', $product->id)->first() ?? $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

$cart->load(['items.product.category', 'items.product.images']);

$itemModel = $cart->items->firstWhere(fn (CartItem $i) => $i->product_id === $product->id);

echo 'relation loaded images count: '.$itemModel->product->getRelation('images')->count()."\n";
echo 'coverImage: '.var_export($itemModel->product->coverImage(), true)."\n";

$html = view('cart.index', [
    'lines' => $cart->items->map(fn (CartItem $i): array => [
        'item' => $i,
        'product' => $i->product,
        'orderable' => $i->product !== null && $i->product->isOrderable(),
        'line_total' => $i->product !== null && $i->product->isOrderable() ? (float) $i->product->price * $i->quantity : 0.0,
    ]),
    'subtotal' => 10000.0,
])->render();

echo 'cart-html-has-img: '.(str_contains($html, 'storage/products') ? 'yes' : 'NO')."\n";

$catalogHtml = view('products.index', [
    'products' => Product::query()->where('status', ProductStatus::Available)->with('category')->get(),
    'categories' => collect(),
    'filters' => ['search' => null, 'category' => null, 'sort' => 'newest'],
    'wishlistProductIds' => [],
])->render();

echo 'catalog-html-has-img: '.(str_contains($catalogHtml, 'storage/products') ? 'yes' : 'NO')."\n";
