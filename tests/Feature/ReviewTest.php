<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_when_posting_review(): void
    {
        $product = Product::factory()->create();

        $response = $this->post(route('reviews.store', $product), [
            'rating' => 5,
            'comment' => 'Enak sekali',
        ]);

        $response->assertRedirect('/masuk');
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_rating_outside_range_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        foreach ([0, 6] as $rating) {
            $response = $this->actingAs($user)->post(route('reviews.store', $product), [
                'rating' => $rating,
                'comment' => 'Ulasan tes',
            ]);

            $response->assertSessionHasErrors('rating');
        }

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_customer_without_completed_purchase_cannot_review(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', $product), [
            'rating' => 4,
            'comment' => 'Ulasan tanpa pembelian',
        ]);

        $response->assertSessionHasErrors('review');
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_customer_with_pending_order_cannot_review(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => OrderStatus::Processing]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => 10000,
            'product_cost' => 5000,
            'quantity' => 1,
            'subtotal' => 10000,
        ]);

        $response = $this->actingAs($user)->post(route('reviews.store', $product), [
            'rating' => 4,
            'comment' => 'Ulasan pesanan belum selesai',
        ]);

        $response->assertSessionHasErrors('review');
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_customer_can_review_product_from_completed_order(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->completed()->create(['user_id' => $user->id]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => 10000,
            'product_cost' => 5000,
            'quantity' => 1,
            'subtotal' => 10000,
        ]);

        $response = $this->actingAs($user)->post(route('reviews.store', $product), [
            'rating' => 5,
            'comment' => 'Enak sekali, recommended!',
        ]);

        $response->assertRedirect(route('products.show', $product));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
        ]);
    }

    public function test_duplicate_review_for_same_product_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->completed()->create(['user_id' => $user->id]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => 10000,
            'product_cost' => 5000,
            'quantity' => 1,
            'subtotal' => 10000,
        ]);

        Review::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => 4,
            'comment' => 'Ulasan pertama',
        ]);

        $response = $this->actingAs($user)->post(route('reviews.store', $product), [
            'rating' => 5,
            'comment' => 'Ulasan kedua',
        ]);

        $response->assertSessionHasErrors('review');
        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_average_rating_is_computed_from_real_data(): void
    {
        $product = Product::factory()->create();
        $users = User::factory()->count(2)->create();
        $order = Order::factory()->completed()->create(['user_id' => $users[0]->id]);

        foreach ([[1, 4], [2, 2]] as [$index, $rating]) {
            Review::query()->create([
                'user_id' => $users[$index - 1]->id,
                'product_id' => $product->id,
                'order_id' => $order->id,
                'rating' => $rating,
            ]);
        }

        $response = $this->get(route('products.show', $product));

        $response->assertOk();
        $response->assertSee('3.0');
        $response->assertSee('2 ulasan');
    }

    public function test_product_page_shows_reviews_and_own_review_state(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->completed()->create(['user_id' => $user->id]);
        Review::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Sangat enak, recommended!',
        ]);

        $response = $this->actingAs($user)->get(route('products.show', $product));

        $response->assertOk();
        $response->assertSee('5.0');
        $response->assertSee('1 ulasan');
        $response->assertSee('Sangat enak, recommended!');
        $response->assertSee('sudah memberikan ulasan');
    }
}
