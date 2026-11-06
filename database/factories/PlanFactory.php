<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => 'Paket '.fake()->unique()->word(),
            'is_custom' => false,
            'durasi_hari' => 90,
            'harga' => 40000,
            'harga_per_minggu' => null,
            'is_active' => true,
        ];
    }

    public function tigaBulan(): static
    {
        return $this->state(fn (array $attributes) => [
            'nama' => '3 Bulan',
            'is_custom' => false,
            'durasi_hari' => 90,
            'harga' => 40000,
            'harga_per_minggu' => null,
            'is_active' => true,
        ]);
    }

    public function limaBulan(): static
    {
        return $this->state(fn (array $attributes) => [
            'nama' => '5 Bulan',
            'is_custom' => false,
            'durasi_hari' => 150,
            'harga' => 65000,
            'harga_per_minggu' => null,
            'is_active' => true,
        ]);
    }

    public function custom(): static
    {
        return $this->state(fn (array $attributes) => [
            'nama' => 'Custom',
            'is_custom' => true,
            'durasi_hari' => null,
            'harga' => null,
            'harga_per_minggu' => 4000,
            'is_active' => true,
        ]);
    }
}
