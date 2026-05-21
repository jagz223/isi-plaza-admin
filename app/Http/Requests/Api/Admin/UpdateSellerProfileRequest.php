<?php

namespace App\Http\Requests\Api\Admin;

use App\Enums\AccessStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'avatar_path' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'description' => ['sometimes', 'nullable', 'string', 'max:100'],
            'country' => ['sometimes', 'nullable', 'string', 'max:120'],
            'state' => ['sometimes', 'nullable', 'string', 'max:120'],
            'whatsapp' => ['sometimes', 'nullable', 'string', 'max:64'],
            'instagram' => ['sometimes', 'nullable', 'string', 'max:255'],
            'facebook' => ['sometimes', 'nullable', 'string', 'max:255'],
            'website' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'is_verified' => ['sometimes', 'boolean'],
            'has_paid_promotion' => ['sometimes', 'boolean'],
            'access_status' => ['sometimes', Rule::enum(AccessStatus::class)],
            'subscription_expires_at' => ['sometimes', 'nullable', 'date'],
            'subscription_granted_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
