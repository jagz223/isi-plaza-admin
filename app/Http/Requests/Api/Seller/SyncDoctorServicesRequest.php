<?php

namespace App\Http\Requests\Api\Seller;

use Illuminate\Foundation\Http\FormRequest;

class SyncDoctorServicesRequest extends FormRequest
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
            'services' => ['required', 'array', 'max:50'],
            'services.*.treatment_id' => ['required', 'integer', 'exists:treatments,id'],
            'services.*.price' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
        ];
    }
}
