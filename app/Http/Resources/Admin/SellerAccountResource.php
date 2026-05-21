<?php

namespace App\Http\Resources\Admin;

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
            'has_password' => filled($this->resource->getRawOriginal('password')),
            'seller_profile' => SellerProfileResource::make($this->whenLoaded('sellerProfile')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
