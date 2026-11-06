<?php

namespace App\Models;

use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $plan_id
 * @property int|null $lapak_id
 * @property string $order_id
 * @property int $jumlah
 * @property int $durasi_hari
 * @property string $status
 * @property string|null $payment_type
 * @property array<string, mixed>|null $form_payload
 * @property Carbon|null $paid_at
 * @property string|null $gateway_status
 * @property Carbon $expires_at
 * @property Carbon $tc_accepted_at
 * @property bool $perlu_tinjauan
 * @property string|null $review_reason
 * @property Carbon|null $created_at
 */
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'plan_id',
        'lapak_id',
        'order_id',
        'jumlah',
        'durasi_hari',
        'payment_type',
        'form_payload',
        'expires_at',
        'tc_accepted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'durasi_hari' => 'integer',
            'form_payload' => 'array',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
            'tc_accepted_at' => 'datetime',
            'perlu_tinjauan' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    /**
     * @return BelongsTo<Lapak, $this>
     */
    public function lapak(): BelongsTo
    {
        return $this->belongsTo(Lapak::class, 'lapak_id');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expire';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancel';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
