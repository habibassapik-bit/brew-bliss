<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontWishlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_wishlist_page(): void
    {
        $response = $this->get('/wishlist');

        $response->assertRedirect('/masuk');
    }

    public function test_guest_cannot_add_product_to_wishlist(): void
    {
        $product = Product::factory()->create();

        $response = $this->post('/wishlist', ['product_id' => $product->id]);

        $response->assertRedirect('/masuk');
        $this->assertGuest();
        $this->assertDatabaseCount('wishlists', 0);
    }

    public function test_customer_can_add_product_to_wishlist(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->post('/wishlist', ['product_id' => $product->id]);

        $response->assertRedirect(route('wishlist.index'));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('wishlists', ['user_id' => $user->id, 'product_id' => $product->id]);
    }

    public function test_adding_same_product_does_not_duplicate_wishlist_item(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)->post('/wishlist', ['product_id' => $product->id]);
        $response = $this->actingAs($user)->post('/wishlist', ['product_id' => $product->id]);

        $response->assertRedirect(route('wishlist.index'));
        $this->assertDatabaseCount('wishlists', 1);
    }

    public function test_customer_cannot_add_inactive_product_to_wishlist(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => ProductStatus::Inactive]);

        $response = $this->actingAs($user)->post('/wishlist', ['product_id' => $product->id]);

        $response->assertSessionHasErrors('product_id');
        $this->assertDatabaseCount('wishlists', 0);
    }

    public function test_customer_can_view_own_wishlist(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Risol Pelangi', 'slug' => 'risol-pelangi']);

        $this->actingAs($user)->post('/wishlist', ['product_id' => $product->id]);

        $response = $this->actingAs($user)->get('/wishlist');

        $response->assertOk();
        $response->assertSee('Risol Pelangi');
        $response->assertSee('Pindahkan ke Keranjang');
    }

    public function test_wishlist_page_shows_empty_state_when_empty(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/wishlist');

        $response->assertOk();
        $response->assertSee('Wishlist kamu masih kosong');
        $response->assertSee('Lihat Katalog');
    }

    public function test_customer_can_remove_own_wishlist_item(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $item = Wishlist::query()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $response = $this->actingAs($user)->delete("/wishlist/{$item->id}");

        $response->assertRedirect(route('wishlist.index'));
        $this->assertDatabaseMissing('wishlists', ['id' => $item->id]);
    }

    public function test_customer_cannot_remove_other_customers_wishlist_item(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $product = Product::factory()->create();
        $item = Wishlist::query()->create(['user_id' => $owner->id, 'product_id' => $product->id]);

        $response = $this->actingAs($other)->delete("/wishlist/{$item->id}");

        $response->assertNotFound();
        $this->assertDatabaseHas('wishlists', ['id' => $item->id]);
    }

    public function test_customer_cannot_move_other_customers_wishlist_item_to_cart(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $product = Product::factory()->create();
        $item = Wishlist::query()->create(['user_id' => $owner->id, 'product_id' => $product->id]);

        $response = $this->actingAs($other)->post("/wishlist/{$item->id}/keranjang");

        $response->assertNotFound();
        $this->assertDatabaseHas('wishlists', ['id' => $item->id]);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_customer_can_move_wishlist_item_to_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);
        $item = Wishlist::query()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $response = $this->actingAs($user)->post("/wishlist/{$item->id}/keranjang");

        $response->assertRedirect(route('cart.index'));
        $this->assertDatabaseMissing('wishlists', ['id' => $item->id]);
        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => 1]);
    }

    public function test_cannot_move_sold_out_wishlist_item_to_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => ProductStatus::SoldOut]);
        $item = Wishlist::query()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $response = $this->actingAs($user)->post("/wishlist/{$item->id}/keranjang");

        $response->assertSessionHasErrors();
        $this->assertDatabaseHas('wishlists', ['id' => $item->id]);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_cannot_move_inactive_wishlist_item_to_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => ProductStatus::Inactive]);
        $item = Wishlist::query()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $response = $this->actingAs($user)->post("/wishlist/{$item->id}/keranjang");

        $response->assertRedirect(route('wishlist.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('wishlists', ['id' => $item->id]);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_catalog_and_homepage_show_wishlist_state_for_logged_in_customer(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        Wishlist::query()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $catalog = $this->actingAs($user)->get('/produk');
        $catalog->assertOk();

        $home = $this->actingAs($user)->get('/');
        $home->assertOk();
        $home->assertSee('Wishlist');
    }

    public function test_wishlist_page_handles_product_deleted_from_wishlist_view(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $item = Wishlist::query()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $product->delete();

        $response = $this->actingAs($user)->get('/wishlist');

        $response->assertOk();
        $response->assertSee('Produk tidak tersedia');
        $response->assertSee('Hapus');

        $remove = $this->actingAs($user)->delete("/wishlist/{$item->id}");
        $remove->assertRedirect(route('wishlist.index'));
        $this->assertDatabaseMissing('wishlists', ['id' => $item->id]);
    }
}
