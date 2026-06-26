<?php

namespace App\Http\Requests\Api\Admin;

use App\Models\Banner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBannerRequest extends FormRequest
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
        /** @var Banner|null $banner */
        $banner = $this->route('banner');
        $categoryId = $this->integer('business_category_id', $banner?->business_category_id);

        return [
            'business_category_id' => ['sometimes', 'integer', 'exists:business_categories,id'],
            'image' => ['sometimes', 'image', 'max:5120'],
            'sort_order' => [
                'sometimes',
                'integer',
                'min:1',
                Rule::unique('banners', 'sort_order')
                    ->where('business_category_id', $categoryId)
                    ->ignore($banner?->id),
            ],
            'is_active' => ['sometimes', 'boolean'],
            'link_url' => ['nullable', 'url', 'max:2048'],
            'treatment_id' => ['nullable', 'integer', 'exists:treatments,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'business_category_id' => 'rubro',
            'image' => 'imagen',
            'sort_order' => 'orden',
            'is_active' => 'activo',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'image.image' => 'El archivo debe ser una imagen válida.',
            'image.max' => 'La imagen no puede superar 5 MB.',
            'image.uploaded' => 'No se pudo subir la imagen.',
            'sort_order.min' => 'El orden debe ser al menos 1.',
            'sort_order.unique' => 'Ya existe un banner en este rubro con ese orden.',
            'is_active.boolean' => 'El estado activo no es válido.',
        ];
    }
}
