<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::factory()->create();
        $order = Order::factory()->completed()->create();
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => $product->price,
            'product_cost' => $product->cost_price,
            'quantity' => 1,
            'subtotal' => $product->price,
        ]);

        return [
            'user_id' => $order->user_id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => fake()->numberBetween(3, 5),
            'comment' => fake()->sentence(),
        ];
    }
}
