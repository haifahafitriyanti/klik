<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    protected $model = Report::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'listing_id' => Listing::factory(),
            'reporter_id' => User::factory()->verified(),
            'alasan' => fake()->randomElement(['tidak_tersedia', 'tidak_sesuai', 'mencurigakan']),
            'catatan' => fake()->sentence(),
            'status' => 'menunggu',
            // active_reporter_id adalah generated column di DB, jangan diset
        ];
    }

    public function menunggu(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'menunggu',
        ]);
    }

    public function ditinjau(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ditinjau',
        ]);
    }

    public function selesai(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'selesai',
        ]);
    }
}
