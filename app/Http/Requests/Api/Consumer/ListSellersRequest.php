<?php

namespace App\Http\Requests\Api\Consumer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListSellersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'business_category_id' => ['sometimes', 'integer', 'exists:business_categories,id'],
            'treatment_id' => ['sometimes', 'integer', 'exists:treatments,id'],
            'country' => ['sometimes', 'nullable', 'string', 'max:100'],
            'state' => ['sometimes', 'nullable', 'string', 'max:100'],
            'region' => ['sometimes', 'string', Rule::in(['cdmx', 'edo_mex'])],
            'municipality' => ['sometimes', 'string', 'max:120'],
            'latitude' => ['sometimes', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['sometimes', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'radius_km' => [
                'sometimes',
                'numeric',
                'min:1',
                'max:'.(int) config('odontica-geo.max_radius_km', 100),
            ],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }
}
