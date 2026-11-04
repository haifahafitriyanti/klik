<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $plan_id
 * @property string $status
 * @property int $harga_saat_beli
 * @property int $durasi_hari
 * @property Carbon $tc_accepted_at
 * @property Carbon $started_at
 * @property Carbon $expires_at
 * @property Carbon|null $paused_at
 * @property Carbon|null $reminder_sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Lapak extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'harga_saat_beli',
        'durasi_hari',
        'tc_accepted_at',
        'started_at',
        'expires_at',
        'paused_at',
        'reminder_sent_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'harga_saat_beli' => 'integer',
            'durasi_hari' => 'integer',
            'tc_accepted_at' => 'datetime',
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'paused_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    /**
     * Seluruh riwayat listing yang pernah diunggah pada lapak ini.
     *
     * @return HasMany<Listing, $this>
     */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'lapak_id');
    }

    /**
     * Listing yang sedang aktif pada lapak saat ini (maksimal 1 sesuai constraint).
     *
     * @return HasOne<Listing, $this>
     */
    public function activeListing(): HasOne
    {
        return $this->hasOne(Listing::class, 'lapak_id')->where('status', 'aktif');
    }

    /**
     * Transaksi pembelian paket yang mengaktifkan lapak ini.
     *
     * @return HasOne<Transaction, $this>
     */
    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class, 'lapak_id');
    }

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    public function isJeda(): bool
    {
        return $this->status === 'jeda';
    }

    public function isHabis(): bool
    {
        return $this->status === 'habis';
    }

    public function isDitutup(): bool
    {
        return $this->status === 'ditutup';
    }
}
