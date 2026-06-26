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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'business_category_id' => ['sometimes', 'nullable', 'exists:business_categories,id'],
            'avatar' => ['sometimes', 'nullable', 'image', 'max:5120'],
            'description' => ['sometimes', 'nullable', 'string', 'max:100'],
            'professional_license' => ['sometimes', 'nullable', 'string', 'max:32'],
            'country' => ['sometimes', 'nullable', 'string', 'max:120'],
            'state' => ['sometimes', 'nullable', 'array'],
            'state.*' => ['string', 'max:120'],
            'address' => ['sometimes', 'nullable', 'string', 'max:500'],
            'municipality' => ['sometimes', 'nullable', 'string', 'max:120'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'whatsapp' => ['sometimes', 'nullable', 'string', 'max:64'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:32'],
            'instagram' => ['sometimes', 'nullable', 'string', 'max:25'],
            'facebook' => ['sometimes', 'nullable', 'string', 'max:25'],
            'website' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'pdf' => ['sometimes', 'nullable', 'file', 'mimes:pdf', 'max:307200'],
            'excel' => ['sometimes', 'nullable', 'file', 'mimes:xlsx,xls', 'max:307200'],
            'carousel_metadata' => ['sometimes', 'nullable', 'array'],
            'carousel_metadata.*.title' => ['sometimes', 'nullable', 'string', 'max:30'],
            'carousel_metadata.*.description' => ['sometimes', 'nullable', 'string', 'max:65'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pdf.uploaded' => 'No se pudo subir el PDF. Comprueba que pese menos de 300 MB y vuelve a intentarlo.',
            'pdf.mimes' => 'El catálogo debe ser un archivo PDF (.pdf).',
            'pdf.max' => 'El PDF no puede superar 300 MB.',
            'excel.uploaded' => 'No se pudo subir el Excel. Comprueba que pese menos de 300 MB y vuelve a intentarlo.',
            'excel.mimes' => 'La lista debe ser un archivo Excel (.xlsx o .xls).',
            'excel.max' => 'El Excel no puede superar 300 MB.',
        ];
    }
}
