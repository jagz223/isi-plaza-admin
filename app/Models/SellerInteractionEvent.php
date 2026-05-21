<?php

namespace App\Models;

use App\Enums\SellerInteractionEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerInteractionEvent extends Model
{
    public $timestamps = false;

    protected static function booted(): void
    {
        static::creating(function (SellerInteractionEvent $event): void {
            $event->created_at ??= now();
        });
    }

    protected $fillable = [
        'seller_user_id',
        'event_type',
        'actor_user_id',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_type' => SellerInteractionEventType::class,
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function sellerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
