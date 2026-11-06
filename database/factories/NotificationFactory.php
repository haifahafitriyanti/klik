<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tipe' => 'info',
            'ref_id' => null,
            'pesan' => fake()->sentence(),
            'dibaca' => false,
        ];
    }

    public function dibaca(): static
    {
        return $this->state(fn (array $attributes) => [
            'dibaca' => true,
        ]);
    }

    public function belumDibaca(): static
    {
        return $this->state(fn (array $attributes) => [
            'dibaca' => false,
        ]);
    }
}
