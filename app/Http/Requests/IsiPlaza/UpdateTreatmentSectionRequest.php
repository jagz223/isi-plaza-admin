<?php

namespace App\Http\Requests\IsiPlaza;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTreatmentSectionRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'update_slug' => ['sometimes', 'boolean'],
        ];
    }
}
