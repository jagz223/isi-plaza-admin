<?php

namespace App\Http\Resources;

use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Treatment
 */
class TreatmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'treatment_section_id' => $this->treatment_section_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'section' => $this->whenLoaded('section', fn () => [
                'id' => $this->section?->id,
                'name' => $this->section?->name,
                'slug' => $this->section?->slug,
            ]),
        ];
    }
}
