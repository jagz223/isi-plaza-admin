<?php

namespace App\Http\Resources\Consumer;

use App\Models\Banner;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Banner
 */
class ConsumerBannerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image_url' => MediaUrl::resolve($this->image_url),
            'sort_order' => $this->sort_order,
            'link_url' => $this->link_url,
            'treatment_id' => $this->treatment_id,
            'treatment_name' => $this->relationLoaded('treatment') ? $this->treatment?->name : null,
        ];
    }
}
