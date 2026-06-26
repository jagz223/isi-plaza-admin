<?php

namespace App\Http\Resources\Seller;

use App\Models\DoctorService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DoctorService
 */
class DoctorServiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'treatment_id' => $this->treatment_id,
            'price' => (float) $this->price,
            'treatment' => $this->whenLoaded('treatment', fn () => [
                'id' => $this->treatment?->id,
                'name' => $this->treatment?->name,
                'slug' => $this->treatment?->slug,
                'treatment_section_id' => $this->treatment?->treatment_section_id,
                'section' => $this->treatment?->relationLoaded('section') && $this->treatment->section !== null
                    ? [
                        'id' => $this->treatment->section->id,
                        'name' => $this->treatment->section->name,
                    ]
                    : null,
            ]),
        ];
    }
}
