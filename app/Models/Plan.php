<?php

namespace App\Models;

use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nama
 * @property bool $is_custom
 * @property int|null $durasi_hari
 * @property int|null $harga
 * @property int|null $harga_per_minggu
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'is_custom',
        'durasi_hari',
        'harga',
        'harga_per_minggu',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_custom' => 'boolean',
            'durasi_hari' => 'integer',
            'harga' => 'integer',
            'harga_per_minggu' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Lapak, $this>
     */
    public function lapaks(): HasMany
    {
        return $this->hasMany(Lapak::class, 'plan_id');
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'plan_id');
    }
}
