<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nama
 * @property int $prioritas
 * @property string $kriteria
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Category extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'prioritas',
        'kriteria',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'prioritas' => 'integer',
        ];
    }

    /**
     * @return HasMany<Listing, $this>
     */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'category_id');
    }
}
