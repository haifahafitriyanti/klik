<?php

namespace App\Models;

use Database\Factories\AppealFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $moderation_action_id
 * @property int $nomor_banding
 * @property string $alasan
 * @property string|null $bukti_path
 * @property Carbon $submitted_at
 * @property string $status
 * @property int|null $decided_by
 * @property string|null $catatan_admin
 * @property Carbon|null $decided_at
 * @property Carbon|null $batas_banding_berikutnya
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Appeal extends Model
{
    /** @use HasFactory<AppealFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'moderation_action_id',
        'nomor_banding',
        'alasan',
        'bukti_path',
        'submitted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nomor_banding' => 'integer',
            'submitted_at' => 'datetime',
            'decided_at' => 'datetime',
            'batas_banding_berikutnya' => 'datetime',
        ];
    }

    /**
     * Tindakan moderasi yang diajukan banding.
     *
     * @return BelongsTo<ModerationAction, $this>
     */
    public function moderationAction(): BelongsTo
    {
        return $this->belongsTo(ModerationAction::class, 'moderation_action_id');
    }

    /**
     * Admin yang memutuskan hasil banding.
     *
     * @return BelongsTo<User, $this>
     */
    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function isMenunggu(): bool
    {
        return $this->status === 'menunggu';
    }

    public function isDiterima(): bool
    {
        return $this->status === 'diterima';
    }

    public function isDitolak(): bool
    {
        return $this->status === 'ditolak';
    }
}
