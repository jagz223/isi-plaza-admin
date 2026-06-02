<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'image' => ['nullable', 'image', 'max:5120'],
            'external_image_url' => ['nullable', 'url', 'max:2048'],
            'sort_order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('banners', 'sort_order')->where('business_category_id', $categoryId),
            ],
            'is_active' => ['sometimes', 'boolean'],
            'link_url' => ['nullable', 'url', 'max:2048'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $hasFile = $this->hasFile('image');
            $hasUrl = filled($this->input('external_image_url'));

            if (! $hasFile && ! $hasUrl) {
                $validator->errors()->add('image', 'Sube una imagen o indica una URL de imagen.');
            }

            if ($hasFile && $hasUrl) {
                $validator->errors()->add('image', 'Usa solo imagen o URL de imagen, no ambas a la vez.');
                $validator->errors()->add('external_image_url', 'Usa solo imagen o URL de imagen, no ambas a la vez.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'business_category_id' => 'rubro',
            'image' => 'imagen',
            'external_image_url' => 'URL de imagen',
            'sort_order' => 'orden',
            'is_active' => 'activo',
            'link_url' => 'URL a redirigir',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'business_category_id.required' => 'Selecciona un rubro.',
            'business_category_id.exists' => 'El rubro seleccionado no es válido.',
            'image.image' => 'El archivo debe ser una imagen válida.',
            'image.max' => 'La imagen no puede superar 5 MB.',
            'image.uploaded' => 'No se pudo subir la imagen.',
            'external_image_url.url' => 'La URL de imagen no es válida.',
            'external_image_url.max' => 'La URL de imagen es demasiado larga.',
            'sort_order.required' => 'Indica el orden del banner.',
            'sort_order.integer' => 'El orden debe ser un número entero.',
            'sort_order.min' => 'El orden debe ser al menos 1.',
            'sort_order.unique' => 'Ya existe un banner en este rubro con ese orden.',
            'is_active.boolean' => 'El estado activo no es válido.',
            'link_url.url' => 'La URL a redirigir no es válida.',
            'link_url.max' => 'La URL a redirigir es demasiado larga.',
        ];
    }
}
