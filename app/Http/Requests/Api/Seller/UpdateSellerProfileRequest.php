<?php

namespace App\Http\Requests\Api\Seller;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSellerProfileRequest extends FormRequest
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
            'business_category_id' => ['sometimes', 'nullable', 'exists:business_categories,id'],
            'avatar' => ['sometimes', 'nullable', 'image', 'max:5120'],
            'description' => ['sometimes', 'nullable', 'string', 'max:100'],
            'country' => ['sometimes', 'nullable', 'string', 'max:120'],
            'state' => ['sometimes', 'nullable', 'array'],
            'state.*' => ['string', 'max:120'],
            'whatsapp' => ['sometimes', 'nullable', 'string', 'max:64'],
            'instagram' => ['sometimes', 'nullable', 'string', 'max:25'],
            'facebook' => ['sometimes', 'nullable', 'string', 'max:25'],
            'website' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'pdf' => ['sometimes', 'nullable', 'file', 'mimes:pdf', 'max:10240'],
            'excel' => ['sometimes', 'nullable', 'file', 'mimes:xlsx,xls', 'max:10240'],
            'carousel_metadata' => ['sometimes', 'nullable', 'array'],
            'carousel_metadata.*.title' => ['sometimes', 'nullable', 'string', 'max:30'],
            'carousel_metadata.*.description' => ['sometimes', 'nullable', 'string', 'max:65'],
        ];
    }
}
