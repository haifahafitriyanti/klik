<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $listing_id
 * @property int $admin_id
 * @property string $kode_alasan
 * @property string $alasan
 * @property Carbon $hidden_at
 * @property Carbon $response_deadline
 * @property string $status
 * @property Carbon|null $resolved_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ModerationAction extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'listing_id',
        'admin_id',
        'kode_alasan',
        'alasan',
        'hidden_at',
        'response_deadline',
        'status',
        'resolved_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hidden_at' => 'datetime',
            'response_deadline' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Listing, $this>
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }

    /**
     * Admin yang melakukan tindakan moderasi.
     *
     * @return BelongsTo<User, $this>
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Riwayat pengajuan banding untuk tindakan moderasi ini.
     *
     * @return HasMany<Appeal, $this>
     */
    public function appeals(): HasMany
    {
        return $this->hasMany(Appeal::class, 'moderation_action_id');
    }

    public function isMenunggu(): bool
    {
        return $this->status === 'menunggu';
    }

    public function isBanding(): bool
    {
        return $this->status === 'banding';
    }

    public function isPulih(): bool
    {
        return $this->status === 'pulih';
    }

    public function isHangus(): bool
    {
        return $this->status === 'hangus';
    }

    public function isTanpaBanding(): bool
    {
        return $this->status === 'tanpa_banding';
    }
}
