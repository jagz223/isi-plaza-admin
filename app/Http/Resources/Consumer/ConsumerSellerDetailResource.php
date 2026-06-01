<?php

namespace App\Http\Resources\Consumer;

use App\Models\User;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'avatar_url' => MediaUrl::resolve($profile?->avatar_url),
            'whatsapp' => $profile?->whatsapp,
            'instagram' => $profile?->instagram,
            'facebook' => $profile?->facebook,
            'website' => $profile?->website,
            'pdf_url' => $profile?->pdf_url
                ? route('api.consumer.sellers.pdf.file', ['seller' => $this->id], absolute: true)
                : null,
            'excel_url' => $profile?->excel_url
                ? route('api.consumer.sellers.excel.file', ['seller' => $this->id], absolute: true)
                : null,
            'carousel_metadata' => $profile?->carousel_metadata ?? [],
            'is_verified' => (bool) ($profile?->is_verified),
            'has_active_promotion' => (bool) ($profile?->has_paid_promotion),
            'business_category' => $profile?->relationLoaded('businessCategory') && $profile->businessCategory !== null
                ? [
                    'id' => $profile->businessCategory->id,
                    'name' => $profile->businessCategory->name,
                    'slug' => $profile->businessCategory->slug,
                ]
                : null,
            'catalog_images' => collect($profile?->catalogImages ?? [])->map(
                fn ($image) => [
                    'id' => $image->id,
                    'image_url' => route('api.consumer.sellers.catalog-images.file', [
                        'seller' => $this->id,
                        'catalogImage' => $image->id,
                    ], absolute: true),
                    'display_order' => $image->display_order,
                ]
            )->values(),
            'is_favorited' => $this->isFavorited,
        ];
    }
}
