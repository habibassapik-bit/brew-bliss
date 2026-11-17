<?php

namespace Database\Factories;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(20000, 100000);

        return [
            'order_number' => 'ORD-'.str_pad((string) fake()->unique()->numberBetween(1, 99999999), 8, '0', STR_PAD_LEFT),
            'user_id' => User::factory(),
            'status' => OrderStatus::PendingConfirmation,
            'payment_status' => PaymentStatus::Unpaid,
            'fulfillment_type' => FulfillmentType::Pickup,
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->e164PhoneNumber(),
            'subtotal' => $subtotal,
            'discount_amount' => 0,
            'delivery_fee' => 0,
            'total' => $subtotal,
        ];
    }

    /**
     * Pesanan yang sudah dibayar.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => now(),
        ]);
    }

    /**
     * Pesanan yang selesai.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Completed,
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => now(),
            'completed_at' => now(),
        ]);
    }

    /**
     * Pesanan yang dibatalkan.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Cancelled,
        ]);
    }
}
