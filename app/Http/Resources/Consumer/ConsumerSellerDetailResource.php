<?php

namespace App\Http\Resources\Consumer;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin User
 */
class ConsumerSellerDetailResource extends JsonResource
{
    public function __construct($resource, protected bool $isFavorited = false)
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
            'country' => $profile?->country,
            'state' => $profile?->state,
            'avatar_url' => $profile?->avatar_path
                ? Storage::disk('public')->url($profile->avatar_path)
                : null,
            'whatsapp' => $profile?->whatsapp,
            'instagram' => $profile?->instagram,
            'facebook' => $profile?->facebook,
            'website' => $profile?->website,
            'is_verified' => (bool) ($profile?->is_verified),
            'has_active_promotion' => (bool) ($profile?->has_paid_promotion),
            'business_category' => $profile?->relationLoaded('businessCategory') && $profile->businessCategory !== null
                ? [
                    'id' => $profile->businessCategory->id,
                    'name' => $profile->businessCategory->name,
                    'slug' => $profile->businessCategory->slug,
                ]
                : null,
            'catalog_images' => ConsumerCatalogImageResource::collection(
                $profile?->catalogImages ?? []
            ),
            'is_favorited' => $this->isFavorited,
        ];
    }
}
