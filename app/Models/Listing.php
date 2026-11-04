<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $lapak_id
 * @property int $category_id
 * @property int $brand_id
 * @property string $judul
 * @property string $model
 * @property string $prosesor
 * @property int $ram_gb
 * @property string $penyimpanan
 * @property bool $gpu_dedicated
 * @property bool $tipe_2in1
 * @property string|null $berat_kg
 * @property int $harga
 * @property string $kondisi
 * @property string $deskripsi
 * @property string $no_wa
 * @property string $status
 * @property Carbon|null $sold_at
 * @property Carbon|null $created_at
 * @property Carbon|null $terakhir_dikonfirmasi
 * @property int|null $active_lapak_id
 */
class Listing extends Model
{
    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'lapak_id',
        'category_id',
        'brand_id',
        'judul',
        'model',
        'prosesor',
        'ram_gb',
        'penyimpanan',
        'gpu_dedicated',
        'tipe_2in1',
        'berat_kg',
        'harga',
        'kondisi',
        'deskripsi',
        'no_wa',
        'status',
        'sold_at',
        'terakhir_dikonfirmasi',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ram_gb' => 'integer',
            'gpu_dedicated' => 'boolean',
            'tipe_2in1' => 'boolean',
            'berat_kg' => 'decimal:2',
            'harga' => 'integer',
            'sold_at' => 'datetime',
            'terakhir_dikonfirmasi' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Lapak, $this>
     */
    public function lapak(): BelongsTo
    {
        return $this->belongsTo(Lapak::class, 'lapak_id');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * @return HasMany<ListingPhoto, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(ListingPhoto::class, 'listing_id');
    }

    /**
     * @return HasMany<Inquiry, $this>
     */
    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'listing_id');
    }

    /**
     * @return HasMany<ModerationAction, $this>
     */
    public function moderationActions(): HasMany
    {
        return $this->hasMany(ModerationAction::class, 'listing_id');
    }

    /**
     * @return HasMany<Report, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'listing_id');
    }

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    public function isTersembunyi(): bool
    {
        return $this->status === 'tersembunyi';
    }

    public function isTerjual(): bool
    {
        return $this->status === 'terjual';
    }

    public function isKedaluwarsa(): bool
    {
        return $this->status === 'kedaluwarsa';
    }
}
