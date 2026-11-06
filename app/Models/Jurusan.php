<?php

namespace App\Models;

use Database\Factories\JurusanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nama
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Jurusan extends Model
{
    /** @use HasFactory<JurusanFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nama',
    ];

    /**
     * @return HasMany<ProdiCode, $this>
     */
    public function prodiCodes(): HasMany
    {
        return $this->hasMany(ProdiCode::class, 'jurusan_id');
    }
}
