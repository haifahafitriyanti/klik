<?php

namespace Database\Factories;

use App\Models\Jurusan;
use App\Models\ProdiCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProdiCode>
 */
class ProdiCodeFactory extends Factory
{
    protected $model = ProdiCode::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'jurusan_id' => Jurusan::factory(),
            'kode' => fake()->unique()->numerify('####'),
            'jenjang' => fake()->randomElement(['D3', 'D4']),
            'nama_prodi' => 'D3 '.fake()->word(),
            'berlaku_dari' => 2015,
            'berlaku_sampai' => null,
            'created_by' => User::factory()->admin(),
        ];
    }
}
