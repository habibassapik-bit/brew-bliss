<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $order = Order::factory()->create();

        return [
            'order_id' => $order->id,
            'method' => fake()->randomElement([PaymentMethod::BankTransfer, PaymentMethod::Qris]),
            'status' => PaymentStatus::PendingVerification,
            'amount' => $order->total,
            'payer_name' => $order->customer_name,
            'reference_number' => fake()->unique()->numerify('REF##########'),
        ];
    }
}
