<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBannerRequest extends FormRequest
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
        $categoryId = $this->integer('business_category_id');

        return [
            'business_category_id' => ['required', 'integer', 'exists:business_categories,id'],
            'image' => ['required', 'image', 'max:5120'],
            'sort_order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('banners', 'sort_order')->where('business_category_id', $categoryId),
            ],
            'is_active' => ['sometimes', 'boolean'],
            'link_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'business_category_id.required' => 'Selecciona un rubro.',
            'sort_order.unique' => 'Ya existe un banner en este rubro con ese orden.',
        ];
    }
}
