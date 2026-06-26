<?php

namespace App\Http\Resources\Seller;

use App\Enums\AccessStatus;
use App\Models\SellerProfile;
use App\Support\MediaUrl;
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
            'avatar_url' => MediaUrl::resolve($this->avatar_url),
            'pdf_url' => MediaUrl::resolve($this->pdf_url),
            'excel_url' => MediaUrl::resolve($this->excel_url),
            'description' => $this->description,
            'professional_license' => $this->professional_license,
            'country' => $this->country,
            'state' => $this->state,
            'address' => $this->address,
            'municipality' => $this->municipality,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'whatsapp' => $this->whatsapp,
            'phone' => $this->phone,
            'instagram' => $this->instagram,
            'facebook' => $this->facebook,
            'website' => $this->website,
            'carousel_metadata' => $this->carousel_metadata ?? [],
            'doctor_services' => DoctorServiceResource::collection(
                $this->whenLoaded('doctorServices')
            ),
            'is_verified' => $this->is_verified,
            'has_paid_promotion' => $this->has_paid_promotion,
            'has_active_promotion' => $this->has_paid_promotion,
            'access_status' => $this->access_status instanceof \BackedEnum ? $this->access_status->value : $this->access_status,
            'has_access' => $this->access_status === AccessStatus::Active,
            'subscription_expires_at' => $this->subscription_expires_at?->toIso8601String(),
            'subscription_granted_at' => $this->subscription_granted_at?->toIso8601String(),
        ];
    }
}
