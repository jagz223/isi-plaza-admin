<?php

namespace App\Http\Requests\IsiPlaza;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTextosNumerosRequest extends FormRequest
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
            'subscription_plan_label' => ['required', 'string', 'max:120'],
            'subscription_price_label' => ['required', 'string', 'max:200'],
            'subscription_message_pending' => ['required', 'string', 'max:500'],
            'subscription_message_active' => ['required', 'string', 'max:500'],
            'subscription_whatsapp_url' => ['required', 'string', 'url', 'max:500'],
            'promotion_whatsapp_url' => ['required', 'string', 'url', 'max:500'],
            'subscribe_button_label' => ['required', 'string', 'max:80'],
            'promotion_button_label' => ['required', 'string', 'max:120'],
        ];
    }
}
