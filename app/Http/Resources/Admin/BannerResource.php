<?php

namespace App\Http\Resources\Admin;

use App\Models\Banner;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Banner
 */
class BannerResource extends JsonResource
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
                'id' => $this->businessCategory->id,
                'name' => $this->businessCategory->name,
            ]),
            'image_url' => MediaUrl::resolve($this->image_url),
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'link_url' => $this->link_url,
            'treatment_id' => $this->treatment_id,
            'treatment' => $this->whenLoaded('treatment', fn () => [
                'id' => $this->treatment?->id,
                'name' => $this->treatment?->name,
            ]),
            'clicks_count' => $this->clicks_count,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
