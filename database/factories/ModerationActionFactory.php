<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\ModerationAction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ModerationAction>
 */
class ModerationActionFactory extends Factory
{
    protected $model = ModerationAction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'listing_id' => Listing::factory(),
            'admin_id' => User::factory()->admin(),
            'kode_alasan' => fake()->randomElement(['M1', 'M2', 'M3', 'M4', 'M5', 'M6', 'M7', 'M8']),
            'alasan' => 'Indikasi pelanggaran '.fake()->sentence(),
            'hidden_at' => now(),
            'response_deadline' => now()->addHours(72),
            'status' => 'menunggu',
            'resolved_at' => null,
        ];
    }

    public function banding(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'banding',
        ]);
    }

    public function pulih(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pulih',
            'resolved_at' => now(),
        ]);
    }

    public function hangus(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'hangus',
            'resolved_at' => now(),
        ]);
    }

    public function tanpaBanding(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'tanpa_banding',
            'resolved_at' => now(),
        ]);
    }
}
