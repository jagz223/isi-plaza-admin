<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Banner extends Model
{
    protected $fillable = [
        'business_category_id',
        'image_url',
        'sort_order',
        'is_active',
        'clicks_count',
        'link_url',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'clicks_count' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<BusinessCategory, $this>
     */
    public function businessCategory(): BelongsTo
    {
        return $this->belongsTo(BusinessCategory::class);
    }
}
