<?php

namespace App\Http\Requests\Api\Consumer;

use App\Enums\SellerInteractionEventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSellerInteractionRequest extends FormRequest
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
            'event_type' => ['required', 'string', Rule::enum(SellerInteractionEventType::class)],
        ];
    }
}
