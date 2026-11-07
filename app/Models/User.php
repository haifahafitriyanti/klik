<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property string|null $nim
 * @property string|null $username
 * @property string $status_mahasiswa
 * @property string|null $kode_prodi_nim
 * @property int|null $angkatan
 * @property int|null $prodi_code_id
 * @property bool $kode_prodi_tidak_dikenal
 * @property string|null $ktm_path
 * @property string $status_verifikasi
 * @property int|null $verified_by
 * @property Carbon|null $verified_at
 * @property string|null $alasan_penolakan
 * @property string $role
 * @property bool $is_suspended
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'name',
    'email',
    'password',
    'nim',
    'username',
    'status_mahasiswa',
    'kode_prodi_nim',
    'angkatan',
    'prodi_code_id',
    'kode_prodi_tidak_dikenal',
    'ktm_path',
])]
#[Hidden([
    'password',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'remember_token',
    'ktm_path',
])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'angkatan' => 'integer',
            'kode_prodi_tidak_dikenal' => 'boolean',
            'verified_at' => 'datetime',
            'is_suspended' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<ProdiCode, $this>
     */
    public function prodiCode(): BelongsTo
    {
        return $this->belongsTo(ProdiCode::class, 'prodi_code_id');
    }

    /**
     * Admin yang memverifikasi akun pengguna ini.
     *
     * @return BelongsTo<User, $this>
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(self::class, 'verified_by');
    }

    /**
     * Daftar pengguna yang diverifikasi oleh admin ini.
     *
     * @return HasMany<User, $this>
     */
    public function verifiedUsers(): HasMany
    {
        return $this->hasMany(self::class, 'verified_by');
    }

    /**
     * @return HasMany<Lapak, $this>
     */
    public function lapaks(): HasMany
    {
        return $this->hasMany(Lapak::class, 'user_id');
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    /**
     * @return HasMany<Inquiry, $this>
     */
    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'buyer_id');
    }

    /**
     * @return HasMany<Report, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    /**
     * @return HasMany<ModerationAction, $this>
     */
    public function moderationActions(): HasMany
    {
        return $this->hasMany(ModerationAction::class, 'admin_id');
    }

    /**
     * @return HasMany<Appeal, $this>
     */
    public function decidedAppeals(): HasMany
    {
        return $this->hasMany(Appeal::class, 'decided_by');
    }

    /**
     * Notifikasi milik pengguna pada sistem KLiK.
     *
     * @return HasMany<Notification, $this>
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /**
     * @return HasMany<ProdiCode, $this>
     */
    public function createdProdiCodes(): HasMany
    {
        return $this->hasMany(ProdiCode::class, 'created_by');
    }

    /**
     * Periksa apakah pengguna memiliki hak akses administrator.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Periksa apakah akun telah diverifikasi.
     */
    public function isVerified(): bool
    {
        return $this->status_verifikasi === 'disetujui';
    }

    /**
     * Periksa apakah akun telah diverifikasi dan disetujui.
     */
    public function isApproved(): bool
    {
        return $this->status_verifikasi === 'disetujui';
    }

    /**
     * Periksa apakah status verifikasi akun masih menunggu peninjauan.
     */
    public function isPendingVerification(): bool
    {
        return $this->status_verifikasi === 'menunggu';
    }

    /**
     * Periksa apakah pengajuan verifikasi akun ditolak.
     */
    public function isRejectedVerification(): bool
    {
        return $this->status_verifikasi === 'ditolak';
    }

    /**
     * Periksa apakah akun dalam status penangguhan/dibekukan.
     */
    public function isSuspended(): bool
    {
        return (bool) $this->is_suspended;
    }

    /**
     * Periksa apakah pengguna berstatus mahasiswa aktif.
     */
    public function isMahasiswaAktif(): bool
    {
        return $this->status_mahasiswa === 'aktif';
    }

    /**
     * Periksa apakah pengguna berstatus alumni.
     */
    public function isAlumni(): bool
    {
        return $this->status_mahasiswa === 'alumni';
    }
}
