<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'sender_id' => User::factory(),
            'sender_role' => 'customer',
            'body' => fake()->sentence(),
        ];
    }

    /**
     * Pesan dari admin/customer service.
     */
    public function fromAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'sender_role' => 'admin',
        ]);
    }
}
