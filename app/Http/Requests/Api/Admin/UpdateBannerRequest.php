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
            'link_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
        ];
    }
}
