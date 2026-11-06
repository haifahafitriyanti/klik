<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => 'Kategori '.fake()->unique()->word(),
            'prioritas' => fake()->numberBetween(1, 5),
            'kriteria' => 'Kriteria '.fake()->sentence(),
        ];
    }
}
