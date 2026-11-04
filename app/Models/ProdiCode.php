<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $jurusan_id
 * @property string $kode
 * @property string $jenjang
 * @property string $nama_prodi
 * @property int|null $berlaku_dari
 * @property int|null $berlaku_sampai
 * @property int $created_by
 * @property Carbon|null $created_at
 */
class ProdiCode extends Model
{
    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'jurusan_id',
        'kode',
        'jenjang',
        'nama_prodi',
        'berlaku_dari',
        'berlaku_sampai',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'berlaku_dari' => 'integer',
            'berlaku_sampai' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Jurusan, $this>
     */
    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'prodi_code_id');
    }
}
