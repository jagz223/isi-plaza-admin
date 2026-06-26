<?php

namespace App\Http\Requests\IsiPlaza;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTreatmentRequest extends FormRequest
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
            'treatment_section_id' => ['sometimes', 'integer', 'exists:treatment_sections,id'],
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'update_slug' => ['sometimes', 'boolean'],
        ];
    }
}
