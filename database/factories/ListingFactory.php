<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Lapak;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Listing>
 */
class ListingFactory extends Factory
{
    protected $model = Listing::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lapak_id' => Lapak::factory(),
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'judul' => fake()->words(4, true),
            'model' => 'Model '.fake()->word(),
            'prosesor' => 'Intel Core i5-'.fake()->numberBetween(8, 13).'th Gen',
            'ram_gb' => fake()->randomElement([8, 16, 32]),
            'penyimpanan' => fake()->randomElement(['256GB SSD', '512GB SSD', '1TB SSD']),
            'gpu_dedicated' => false,
            'tipe_2in1' => false,
            'berat_kg' => '1.50',
            'harga' => fake()->numberBetween(2500000, 8000000),
            'kondisi' => 'Mulus '.fake()->numberBetween(85, 98).'%',
            'deskripsi' => fake()->paragraph(),
            'no_wa' => '0812'.fake()->numerify('########'), // Nomor simulasi dummy
            'status' => 'aktif',
            'sold_at' => null,
            'terakhir_dikonfirmasi' => null,
        ];
    }

    public function aktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'aktif',
            'sold_at' => null,
        ]);
    }

    public function tersembunyi(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'tersembunyi',
        ]);
    }

    public function terjual(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'terjual',
            'sold_at' => now(),
        ]);
    }

    public function kedaluwarsa(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'kedaluwarsa',
        ]);
    }
}
