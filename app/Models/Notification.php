<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Model Notifikasi KLiK.
 *
 * @property int $id
 * @property int $user_id
 * @property string $tipe
 * @property int|null $ref_id
 * @property string $pesan
 * @property bool $dibaca
 * @property Carbon|null $created_at
 */
class Notification extends Model
{
    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'tipe',
        'ref_id',
        'pesan',
        'dibaca',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ref_id' => 'integer',
            'dibaca' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
