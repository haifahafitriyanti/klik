<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<User>
     */
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * Menggunakan format NIM dan data dummy simulasi (bukan data orang nyata).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = fake()->numberBetween(20, 24);
        $prodiCode = '0109'; // D3 Manajemen Informatika default
        $jalur = fake()->numberBetween(1, 3);
        $urut = fake()->numberBetween(1, 999);
        $dummyNim = sprintf('%02d%s%d%03d', $year, $prodiCode, $jalur, $urut);

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,

            // Atribut KLiK
            'nim' => $dummyNim,
            'username' => fake()->unique()->userName(),
            'status_mahasiswa' => 'aktif',
            'kode_prodi_nim' => $prodiCode,
            'angkatan' => 2000 + $year,
            'prodi_code_id' => null,
            'kode_prodi_tidak_dikenal' => false,
            'ktm_path' => 'ktm/dummy_ktm_'.fake()->uuid().'.jpg',
            'status_verifikasi' => 'menunggu',
            'verified_by' => null,
            'verified_at' => null,
            'alasan_penolakan' => null,
            'role' => 'user',
            'is_suspended' => false,
        ];
    }

    /**
     * State untuk akun Administrator.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'status_verifikasi' => 'disetujui',
            'verified_at' => now(),
        ]);
    }

    /**
     * State untuk akun yang sudah diverifikasi admin.
     */
    public function verified(?int $verifiedBy = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status_verifikasi' => 'disetujui',
            'verified_at' => now(),
            'verified_by' => $verifiedBy,
        ]);
    }

    /**
     * State untuk akun yang ditolak verifikasinya.
     */
    public function rejected(string $alasan = 'Foto KTM buram dan tidak terbaca'): static
    {
        return $this->state(fn (array $attributes) => [
            'status_verifikasi' => 'ditolak',
            'alasan_penolakan' => $alasan,
            'verified_at' => now(),
        ]);
    }

    /**
     * State untuk akun berstatus Alumni.
     */
    public function alumni(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_mahasiswa' => 'alumni',
            'angkatan' => 2018,
        ]);
    }

    /**
     * State untuk akun yang dibekukan (suspended).
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_suspended' => true,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
