<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_add_product_to_cart(): void
    {
        $product = Product::factory()->create();

        $response = $this->post('/keranjang/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertRedirect('/masuk');
        $this->assertGuest();
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_guest_cannot_open_cart_page(): void
    {
        $response = $this->get('/keranjang');

        $response->assertRedirect('/masuk');
    }

    public function test_customer_can_add_product_to_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10000, 'stock' => 50]);

        $response = $this->actingAs($user)->post('/keranjang/items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'note' => 'Tanpa saus',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'note' => 'Tanpa saus',
        ]);
    }

    public function test_adding_same_product_increases_existing_item_quantity(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 50]);

        $this->actingAs($user)->post('/keranjang/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->actingAs($user)->post('/keranjang/items', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => 5]);
    }

    public function test_adding_more_than_stock_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        $response = $this->actingAs($user)->post('/keranjang/items', [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_adding_beyond_remaining_stock_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        $this->actingAs($user)->post('/keranjang/items', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response = $this->actingAs($user)->post('/keranjang/items', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => 3]);
    }

    public function test_cannot_add_unavailable_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => ProductStatus::SoldOut]);

        $response = $this->actingAs($user)->post('/keranjang/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_cannot_add_inactive_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => ProductStatus::Inactive]);

        $response = $this->actingAs($user)->post('/keranjang/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_customer_can_update_item_quantity(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 50]);

        $this->actingAs($user)->post('/keranjang/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $item = CartItem::query()->where('product_id', $product->id)->first();

        $response = $this->actingAs($user)->patch("/keranjang/items/{$item->id}", [
            'quantity' => 4,
        ]);

        $response->assertRedirect(route('cart.index'));
        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 4]);
    }

    public function test_update_beyond_stock_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        $this->actingAs($user)->post('/keranjang/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $item = CartItem::query()->where('product_id', $product->id)->first();

        $response = $this->actingAs($user)->patch("/keranjang/items/{$item->id}", [
            'quantity' => 10,
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 1]);
    }

    public function test_customer_cannot_update_other_customers_item(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $product = Product::factory()->create(['stock' => 50]);

        $this->actingAs($owner)->post('/keranjang/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $item = CartItem::query()->where('product_id', $product->id)->first();

        $response = $this->actingAs($other)->patch("/keranjang/items/{$item->id}", [
            'quantity' => 1,
        ]);

        $response->assertNotFound();
    }

    public function test_customer_can_remove_item_from_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)->post('/keranjang/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $item = CartItem::query()->where('product_id', $product->id)->first();

        $response = $this->actingAs($user)->delete("/keranjang/items/{$item->id}");

        $response->assertRedirect(route('cart.index'));
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_cart_page_shows_subtotal(): void
    {
        $user = User::factory()->create();
        $productA = Product::factory()->create(['name' => 'Risol Pelangi', 'slug' => 'risol-pelangi', 'price' => 5000]);
        $productB = Product::factory()->create(['name' => 'Es Jeruk', 'slug' => 'es-jeruk', 'price' => 8000]);

        $this->actingAs($user)->post('/keranjang/items', ['product_id' => $productA->id, 'quantity' => 2]);
        $this->actingAs($user)->post('/keranjang/items', ['product_id' => $productB->id, 'quantity' => 1]);

        $response = $this->actingAs($user)->get('/keranjang');

        $response->assertOk();
        $response->assertSee('Risol Pelangi');
        $response->assertSee('Es Jeruk');
        $response->assertSee('Rp 10.000');
        $response->assertSee('Rp 8.000');
        $response->assertSee('Rp 18.000');
    }

    public function test_cart_page_shows_admin_uploaded_product_image(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Es Jeruk', 'slug' => 'es-jeruk', 'image' => 'products/admin-upload.jpg']);

        $this->actingAs($user)->post('/keranjang/items', ['product_id' => $product->id, 'quantity' => 1]);

        $response = $this->actingAs($user)->get('/keranjang');

        $response->assertOk();
        $response->assertSee('storage/products/admin-upload.jpg');
    }

    public function test_cart_page_shows_fallback_for_product_without_image(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Risol Pelangi', 'slug' => 'risol-pelangi', 'image' => null]);

        $this->actingAs($user)->post('/keranjang/items', ['product_id' => $product->id, 'quantity' => 1]);

        $response = $this->actingAs($user)->get('/keranjang');

        $response->assertOk();
        $this->assertStringNotContainsString('storage/products', (string) $response->getContent());
    }
}
