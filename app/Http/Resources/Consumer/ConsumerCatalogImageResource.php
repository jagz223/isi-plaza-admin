<?php

namespace App\Http\Resources\Consumer;

use App\Models\CatalogImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin CatalogImage
 */
class ConsumerCatalogImageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image_url' => Storage::disk('public')->url($this->image_path),
            'display_order' => $this->display_order,
        ];
    }
}
