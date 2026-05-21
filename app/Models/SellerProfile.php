<?php

namespace App\Models;

use App\Enums\AccessStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SellerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'business_category_id',
        'avatar_path',
        'description',
        'country',
        'state',
        'whatsapp',
        'instagram',
        'facebook',
        'website',
        'is_verified',
        'has_paid_promotion',
        'access_status',
        'subscription_expires_at',
        'subscription_granted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'has_paid_promotion' => 'boolean',
            'access_status' => AccessStatus::class,
            'subscription_expires_at' => 'datetime',
            'subscription_granted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<BusinessCategory, $this>
     */
    public function businessCategory(): BelongsTo
    {
        return $this->belongsTo(BusinessCategory::class, 'business_category_id');
    }

    /**
     * @return HasMany<CatalogImage, $this>
     */
    public function catalogImages(): HasMany
    {
        return $this->hasMany(CatalogImage::class, 'seller_profile_id')->orderBy('display_order');
    }

    /**
     * Perfiles visibles en App 1 (solo mayoristas con acceso activo).
     *
     * @param  Builder<SellerProfile>  $query
     * @return Builder<SellerProfile>
     */
    public function scopeVisibleToConsumers(Builder $query): Builder
    {
        return $query->where('access_status', AccessStatus::Active);
    }
}
