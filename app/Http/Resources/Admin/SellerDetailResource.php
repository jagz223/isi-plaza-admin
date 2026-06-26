<?php

namespace App\Http\Resources\Admin;

use App\Models\User;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class SellerDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $profile = $this->sellerProfile;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'seller_profile' => $profile ? [
                'id' => $profile->id,
                'description' => $profile->description,
                'professional_license' => $profile->professional_license,
                'phone' => $profile->phone,
                'whatsapp' => $profile->whatsapp,
                'address' => $profile->address,
                'municipality' => $profile->municipality,
                'latitude' => $profile->latitude,
                'longitude' => $profile->longitude,
                'country' => $profile->country,
                'state' => $profile->state,
                'avatar_url' => MediaUrl::resolve($profile->avatar_url),
                'is_verified' => $profile->is_verified,
                'has_paid_promotion' => $profile->has_paid_promotion,
                'access_status' => $profile->access_status instanceof \BackedEnum
                    ? $profile->access_status->value
                    : $profile->access_status,
                'business_category' => $profile->relationLoaded('businessCategory') && $profile->businessCategory
                    ? ['id' => $profile->businessCategory->id, 'name' => $profile->businessCategory->name]
                    : null,
                'catalog_images' => $profile->relationLoaded('catalogImages')
                    ? $profile->catalogImages->map(fn ($img) => [
                        'id' => $img->id,
                        'image_url' => MediaUrl::resolve($img->image_url),
                        'display_order' => $img->display_order,
                    ])->values()
                    : [],
                'doctor_services' => $profile->relationLoaded('doctorServices')
                    ? $profile->doctorServices->map(fn ($service) => [
                        'id' => $service->id,
                        'treatment_id' => $service->treatment_id,
                        'price' => (float) $service->price,
                        'treatment_name' => $service->treatment?->name,
                        'section_name' => $service->treatment?->section?->name,
                    ])->values()
                    : [],
            ] : null,
        ];
    }
}
