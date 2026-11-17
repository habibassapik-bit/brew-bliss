<?php

namespace Database\Factories;

use App\Enums\VoucherType;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Voucher>
 */
class VoucherFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('??????')),
            'type' => VoucherType::Fixed,
            'value' => fake()->numberBetween(5000, 20000),
            'min_purchase' => 0,
            'max_discount' => null,
            'quota' => null,
            'used_count' => 0,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(7),
            'is_active' => true,
        ];
    }

    /**
     * Voucher berbasis persentase dengan batas maksimum diskon.
     */
    public function percent(int $value = 10, ?int $maxDiscount = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => VoucherType::Percent,
            'value' => $value,
            'max_discount' => $maxDiscount,
        ]);
    }

    /**
     * Voucher yang sudah kedaluwarsa.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => now()->subDays(14),
            'ends_at' => now()->subDay(),
        ]);
    }

    /**
     * Voucher nonaktif.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Voucher dengan kuota terbatas.
     */
    public function withQuota(int $quota, int $usedCount = 0): static
    {
        return $this->state(fn (array $attributes) => [
            'quota' => $quota,
            'used_count' => $usedCount,
        ]);
    }
}
