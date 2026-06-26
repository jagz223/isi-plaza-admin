<?php

namespace App\Http\Resources\Admin;

use App\Models\SellerProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SellerProfile
 */
class SellerProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_category_id' => $this->business_category_id,
            'business_category' => $this->whenLoaded('businessCategory', fn () => [
                'id' => $this->businessCategory?->id,
                'name' => $this->businessCategory?->name,
                'slug' => $this->businessCategory?->slug,
            ]),
            'avatar_url' => $this->avatar_url,
            'description' => $this->description,
            'professional_license' => $this->professional_license,
            'phone' => $this->phone,
            'address' => $this->address,
            'municipality' => $this->municipality,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'country' => $this->country,
            'state' => $this->state,
            'whatsapp' => $this->whatsapp,
            'instagram' => $this->instagram,
            'facebook' => $this->facebook,
            'website' => $this->website,
            'is_verified' => $this->is_verified,
            'has_paid_promotion' => $this->has_paid_promotion,
            'access_status' => $this->access_status instanceof \BackedEnum ? $this->access_status->value : $this->access_status,
            'subscription_expires_at' => $this->subscription_expires_at?->toIso8601String(),
            'subscription_granted_at' => $this->subscription_granted_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
