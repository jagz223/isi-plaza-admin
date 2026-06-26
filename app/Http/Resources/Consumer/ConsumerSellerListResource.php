<?php

namespace App\Http\Resources\Consumer;

use App\Models\User;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class ConsumerSellerListResource extends JsonResource
{
    /**
     * @param  array<int, int>  $favoriteMayoristaIds
     */
    public function __construct($resource, protected array $favoriteMayoristaIds = [])
    {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $profile = $this->sellerProfile;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $profile?->description,
            'professional_license' => $profile?->professional_license,
            'address' => $profile?->address,
            'municipality' => $profile?->municipality,
            'country' => $profile?->country,
            'state' => $profile?->state,
            'avatar_url' => MediaUrl::resolve($profile?->avatar_url),
            'is_verified' => (bool) ($profile?->is_verified),
            'has_active_promotion' => (bool) ($profile?->has_paid_promotion),
            'business_category' => $profile?->relationLoaded('businessCategory') && $profile->businessCategory !== null
                ? [
                    'id' => $profile->businessCategory->id,
                    'name' => $profile->businessCategory->name,
                    'slug' => $profile->businessCategory->slug,
                ]
                : null,
            'is_favorited' => in_array($this->id, $this->favoriteMayoristaIds, true),
            'distance_km' => isset($this->distance_km)
                ? round((float) $this->distance_km, 2)
                : null,
        ];
    }
}
