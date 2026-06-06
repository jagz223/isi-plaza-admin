<?php

namespace App\Http\Requests\Api\Consumer;

use Illuminate\Foundation\Http\FormRequest;

class GuestRegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'whatsapp' => ['required', 'string', 'max:32', 'regex:/^\+[\d\s]{7,29}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'whatsapp.regex' => 'Ingresa un WhatsApp válido con prefijo internacional (ej. +52 5512345678).',
        ];
    }
}
