<?php

namespace Database\Factories;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feedback>
 */
class FeedbackFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category' => fake()->randomElement(['food_quality', 'taste', 'price', 'service', 'delivery', 'website', 'other']),
            'subject' => fake()->sentence(3),
            'message' => fake()->paragraph(),
            'is_anonymous' => false,
            'status' => 'new',
        ];
    }
}
