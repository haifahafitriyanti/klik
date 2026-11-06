<?php

namespace Database\Factories;

use App\Models\Lapak;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lapak>
 */
class LapakFactory extends Factory
{
    protected $model = Lapak::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->verified(),
            'plan_id' => Plan::factory(),
            'status' => 'aktif',
            'harga_saat_beli' => 40000,
            'durasi_hari' => 90,
            'tc_accepted_at' => now(),
            'started_at' => now(),
            'expires_at' => now()->addDays(90),
            'paused_at' => null,
            'reminder_sent_at' => null,
        ];
    }

    public function aktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'aktif',
        ]);
    }

    public function jeda(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'jeda',
            'paused_at' => now(),
        ]);
    }

    public function habis(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'habis',
            'expires_at' => now()->subDay(),
        ]);
    }

    public function ditutup(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ditutup',
        ]);
    }
}
