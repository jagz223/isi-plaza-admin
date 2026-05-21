<?php

namespace App\Http\Resources\Seller;

use App\Enums\AccessStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class SellerAccountResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role instanceof \BackedEnum ? $this->role->value : $this->role,
            'seller_profile' => SellerProfileResource::make($this->whenLoaded('sellerProfile')),
            'has_access' => $this->sellerProfile?->access_status === AccessStatus::Active,
            'subscription_expires_at' => $this->sellerProfile?->subscription_expires_at?->toIso8601String(),
            'is_verified' => (bool) ($this->sellerProfile?->is_verified),
            'has_active_promotion' => (bool) ($this->sellerProfile?->has_paid_promotion),
            'catalog_images' => CatalogImageResource::collection(
                $this->sellerProfile?->catalogImages ?? []
            ),
        ];
    }
}
