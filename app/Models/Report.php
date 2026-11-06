<?php

namespace App\Models;

use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $listing_id
 * @property int $reporter_id
 * @property string $alasan
 * @property string|null $catatan
 * @property string $status
 * @property Carbon|null $created_at
 * @property int|null $active_reporter_id
 */
class Report extends Model
{
    /** @use HasFactory<ReportFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'listing_id',
        'reporter_id',
        'alasan',
        'catatan',
        'status',
    ];

    /**
     * @return BelongsTo<Listing, $this>
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }

    /**
     * Pengguna yang membuat laporan ini.
     *
     * @return BelongsTo<User, $this>
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function isMenunggu(): bool
    {
        return $this->status === 'menunggu';
    }

    public function isDitinjau(): bool
    {
        return $this->status === 'ditinjau';
    }

    public function isSelesai(): bool
    {
        return $this->status === 'selesai';
    }

    /**
     * Laporan berstatus 'menunggu' atau 'ditinjau' dikategorikan aktif.
     */
    public function isAktif(): bool
    {
        return in_array($this->status, ['menunggu', 'ditinjau'], true);
    }
}
