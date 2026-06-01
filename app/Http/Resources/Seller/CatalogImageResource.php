<?php

namespace App\Http\Resources\Seller;

use App\Models\CatalogImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CatalogImage
 */
class CatalogImageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image_url' => route('api.seller.catalog-images.file', $this->id, absolute: true),
            'display_order' => $this->display_order,
        ];
    }
}
