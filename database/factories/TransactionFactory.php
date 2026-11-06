<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->verified(),
            'plan_id' => Plan::factory(),
            'lapak_id' => null,
            'order_id' => 'KLIK-'.fake()->unique()->numerify('##########'),
            'jumlah' => 40000,
            'durasi_hari' => 90,
            'status' => 'pending',
            'payment_type' => 'qris',
            'form_payload' => [
                'judul' => 'Laptop Dummy Test',
                'harga' => 3500000,
            ],
            'paid_at' => null,
            'gateway_status' => null,
            'expires_at' => now()->addMinutes(30),
            'tc_accepted_at' => now(),
            'perlu_tinjauan' => false,
            'review_reason' => null,
        ];
    }

    public function paid(?int $lapakId = null): static
    {
        return $this->state(fn (array $attributes) => array_filter([
            'status' => 'paid',
            'paid_at' => now(),
            'lapak_id' => $lapakId ?? ($attributes['lapak_id'] ?? null),
            'gateway_status' => 'settlement',
        ], fn ($val) => $val !== null));
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expire',
            'expires_at' => now()->subMinutes(5),
            'gateway_status' => 'expire',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancel',
            'gateway_status' => 'cancel',
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'gateway_status' => 'deny',
        ]);
    }

    public function perluTinjauan(string $reason = 'Pembayaran terlambat setelah transaksi dibatalkan'): static
    {
        return $this->state(fn (array $attributes) => [
            'perlu_tinjauan' => true,
            'review_reason' => $reason,
        ]);
    }
}
