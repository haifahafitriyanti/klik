<?php

namespace Database\Factories;

use App\Models\Appeal;
use App\Models\ModerationAction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appeal>
 */
class AppealFactory extends Factory
{
    protected $model = Appeal::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'moderation_action_id' => ModerationAction::factory(),
            'nomor_banding' => 1, // Dibatasi 1-3 oleh CHECK constraint
            'alasan' => 'Penjelasan banding: '.fake()->sentence(),
            'bukti_path' => 'appeals/dummy_proof_'.fake()->uuid().'.jpg',
            'submitted_at' => now(),
            'status' => 'menunggu',
            'decided_by' => null,
            'catatan_admin' => null,
            'decided_at' => null,
            'batas_banding_berikutnya' => null,
        ];
    }

    public function nomor(int $nomor): static
    {
        return $this->state(fn (array $attributes) => [
            'nomor_banding' => $nomor,
        ]);
    }

    public function diterima(?int $adminId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'diterima',
            'decided_by' => $adminId ?? User::factory()->admin(),
            'catatan_admin' => 'Banding diterima, listing dipulihkan.',
            'decided_at' => now(),
        ]);
    }

    public function ditolak(?int $adminId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ditolak',
            'decided_by' => $adminId ?? User::factory()->admin(),
            'catatan_admin' => 'Banding ditolak karena bukti tidak sesuai.',
            'decided_at' => now(),
            'batas_banding_berikutnya' => now()->addHours(72),
        ]);
    }
}
