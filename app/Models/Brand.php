<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nama
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Brand extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'nama',
    ];

    /**
     * @return HasMany<Listing, $this>
     */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'brand_id');
    }
}
